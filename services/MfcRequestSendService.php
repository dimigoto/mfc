<?php

declare(strict_types=1);

namespace common\modules\mfc\services;

use common\components\dateTimeUtils\interfaces\DateTimeUtilsInterface;
use common\components\Mailer;
use common\components\SoapManager;
use common\exceptions\LockException;
use common\exceptions\NotFoundException;
use common\exceptions\RemoteServiceException;
use common\exceptions\DatabaseException;
use common\Helpers\FileHelper;
use common\Helpers\JsonHelper;
use common\models\ExternalService;
use common\modules\mfc\MfcModule;
use common\modules\mfc\models\MfcEnquiryType;
use common\modules\mfc\models\MfcRequest;
use common\modules\upload\services\UploadService;
use common\modules\userRequest\interfaces\LogUserRequestServiceInterface;
use common\modules\userRequest\models\UserRequest;
use common\modules\userRequest\UserRequestModule;
use common\modules\userUniver\models\User;
use common\modules\userUniver\services\UserProfileINNService;
use common\responses\OneCResponse;
use common\services\LockService;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use stdClass;
use Throwable;
use Yii;

/**
 * Сервис для отправки заявки "Единого окна"
 */
class MfcRequestSendService
{
    private const PROCESS_NAME = 'mfc_request_sending';

    private DateTimeUtilsInterface $dateTimeUtils;
    private SoapManager $soapManager;
    private Mailer $mailer;
    private UploadService $uploadService;
    private MfcRequestService $mfcRequestService;
    private LockService $lockService;
    private LogUserRequestServiceInterface $logUserRequestService;
    private UserProfileINNService $userProfileINNService;

    public function __construct(
        DateTimeUtilsInterface $dateTimeUtils,
        SoapManager $soapManager,
        Mailer $mailer,
        UserProfileINNService $userProfileINNService,
        UploadService $uploadService,
        MfcRequestService $mfcRequestService,
        LockService $lockService,
        LogUserRequestServiceInterface $logUserRequestService
    ) {
        $this->dateTimeUtils = $dateTimeUtils;
        $this->soapManager = $soapManager;
        $this->mailer = $mailer;
        $this->uploadService = $uploadService;
        $this->mfcRequestService = $mfcRequestService;
        $this->lockService = $lockService;
        $this->logUserRequestService = $logUserRequestService;
        $this->userProfileINNService = $userProfileINNService;
    }

    /**
     * Отправка заявки "Единого окна"
     *
     * @param MfcRequest $mfcRequest
     *
     * @throws LockException
     * @throws NotFoundException
     * @throws RemoteServiceException
     * @throws DatabaseException
     */
    public function sendMfcRequest(MfcRequest $mfcRequest): void
    {
        if (!$mfcRequest->isStatusCreated()) {
            throw new InvalidArgumentException(
                UserRequestModule::t('common', 'ERROR_REQUEST_IS_SENT_ALREADY')
            );
        }

        $this->lock($mfcRequest->id);

        try {
            ++$mfcRequest->attempts;

            $this->mfcRequestService->updateMfcRequest($mfcRequest);

            $request = $this->make1CRequest($mfcRequest);

            //dd(JsonHelper::jsonEncodePrettyPrint($request));

            $response = $this->sendRequest($request);

            $sendResult = $this->getResponseData($response);

            $mfcRequest->number_foreign = $sendResult['NumberStatement'];
            $mfcRequest->created_foreign = $this->dateTimeUtils->dateTimeToISO(
                $this->dateTimeUtils->timestampToDateTimeImmutable(
                    strtotime($sendResult['DateStatement'])
                )
            );
            $mfcRequest->status = UserRequest::STATUS_SENT;

            $this->mfcRequestService->updateMfcRequest($mfcRequest);

            $this->logUserRequestService->logUserRequestSent($mfcRequest->id);
        } catch (Exception $e) {
            Yii::$app->errorHandler->logException($e);

            if ($this->shouldWePayAttentionTo($e, $mfcRequest)) {
                $this->sendAlarmToAdmin(
                    UserRequestModule::t(
                        'common',
                        'ERROR_REQUEST_SENDING_FAIL_MESSAGE',
                        [
                            'attempts' => $mfcRequest->attempts,
                            'id' => $mfcRequest->id,
                            'message' => $e->getMessage(),
                        ]
                    )
                );
            }

            $this->logUserRequestService->logUserRequestNotSentError(
                $mfcRequest->id,
                $mfcRequest->user->username,
                $e
            );

            throw $e;
        } finally {
            $this->unlock($mfcRequest->id);
        }
    }

    /**
     * @param array $request
     *
     * @return stdClass
     * @throws RemoteServiceException
     */
    private function sendRequest(array $request): stdClass
    {
        $this->soapManager->isLogging = false;

        try {
            return $this->soapManager
                ->service(new ExternalService('itil'))
                ->withParams($request)
                ->setMethodName('CreateRequest')
                ->execute();
        } catch (Throwable $e) {
            Yii::error($e->getMessage());

            throw new RemoteServiceException($e->getMessage());
        }
    }

    /**
     * @param stdClass $response
     *
     * @return array
     * @throws RemoteServiceException
     */
    private function getResponseData(stdClass $response): array
    {
        $oneCResponse = new OneCResponse($response);

        if ($oneCResponse->hasErrors()) {
            Yii::error($oneCResponse->getLastError());

            throw new RemoteServiceException(
                $oneCResponse->getLastError()
            );
        }

        $result = $oneCResponse->getData();

        if (empty($result['DateStatement'])) {
            throw new RemoteServiceException('В ответе 1С не указана дата сохранения заявки');
        }

        if (empty($result['NumberStatement'])) {
            throw new RemoteServiceException('В ответе 1С не указан номер заявки');
        }

        return $result;
    }

    /**
     * @param MfcRequest $mfcRequest
     *
     * @return array
     */
    private function make1CRequest(MfcRequest $mfcRequest): array
    {
        try {
            $enquiryType = $mfcRequest->enquiryType;

            //TODO: Приходится явно читать схему. Надо придумать, как это сделать неявным,
            // не выполняя лишних чтений в других сценариях
            $enquiryType->getStructure();

            $data = JsonHelper::jsonDecode($mfcRequest->data);
            $data['comment'] = $mfcRequest->comment;
            $data['phone'] = $mfcRequest->phone;

            return [
                'Initiator' => $this->makeInitiatorData($mfcRequest->user),
                'Detail' => $this->makeEnquiryData($enquiryType, $data),
                'Service_GUID' => $enquiryType->getGuidEnquiryType(),
            ];
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);

            throw new RuntimeException(
                MfcModule::t(
                    'common',
                    'ERROR_ENQUIRY_NOT_SENT',
                    ['message' => $e->getMessage()]
                )
            );
        }
    }

    /**
     * Данные пользователя
     *
     * @param User $user
     *
     * @return string
     */
    private function makeInitiatorData(User $user): string
    {
        $userProfile = $user->userProfile;
        $employeeProfile = $userProfile->priorityEmployeeProfile;

        $departmentGuid = null;
        $departmentName = null;
        $employeePostGuid = null;
        $employeePostName = null;

        if ($employeeProfile) {
            if (isset($employeeProfile->department)) {
                $departmentGuid = $employeeProfile->department->guid;
                $departmentName = $employeeProfile->department->name;
            }

            if (isset($employeeProfile->employeePost)) {
                $employeePostGuid = $employeeProfile->employeePost->guid;
                $employeePostName = $employeeProfile->employeePost->name;
            }
        }

        try {
            $inn = $this->userProfileINNService->findValue($userProfile->id);
        } catch (NotFoundException $e) {
            $inn = null;
        }

        $data = [
            'Human_GUID' => $userProfile->guid,
            'Login' => $user->username,
            'LastName' => (string)$userProfile->last_name,
            'FirstName' => (string)$userProfile->first_name,
            'MiddleName' => (string)$userProfile->middle_name,
            'Email' => (string)$userProfile->email,
            'Mobile' => (string)$userProfile->mobile,
            'Birthday' => (string)$userProfile->birthday,
            'Sex' => (string)$userProfile->sex,
            'Inn' => (string)$inn,
            'Department_GUID' => (string)$departmentGuid,
            'Department' => (string)$departmentName,
            'Post_GUID' => (string)$employeePostGuid,
            'Post' => (string)$employeePostName,
        ];

        return JsonHelper::jsonEncode($data);
    }

    /**
     * Данные для отправки
     *
     * @param MfcEnquiryType $enquiryType
     * @param array $data Данные из заявки
     *
     * @return string
     */
    private function makeEnquiryData(MfcEnquiryType $enquiryType, array $data): string
    {
        $result = $this->makeEnquiryDataItems($data);
        $result['subject'] = $enquiryType->getTitle();
        $result['enquiryId'] = $enquiryType->getId();

        //dd(JsonHelper::jsonEncodePrettyPrint($result));

        return JsonHelper::jsonEncode($result);
    }

    /**
     * Данные для отправки
     *
     * @param array $data Данные из заявки
     *
     * @return array
     */
    private function makeEnquiryDataItems(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (empty($value['path'])) {
                    $result[$key] = $this->makeEnquiryDataItems($value);
                } else {
                    $result[$key] = [
                        'fileName' => FileHelper::getFileNameWithExtension(
                            $value['base_name'],
                            $value['type']
                        ),
                        'src' => base64_encode(
                            file_get_contents(
                                $this->uploadService->getAbsolutePath($value['path'])
                            )
                        ),
                    ];
                }
            } else {
                $result[$key] = $value;

                if (null === $result[$key]) {
                    $result[$key] = '';
                }
            }
        }

        return $result;
    }

    /**
     * Установка блокировки
     *
     * @throws LockException
     */
    private function lock(int $mfcRequestId): void
    {
        $this->lockService->lockProcess(
            $this->getProcessName($mfcRequestId)
        );
    }

    /**
     * Снятие блокировки
     */
    private function unlock(int $mfcRequestId): void
    {
        $this->lockService->unlockProcess(
            $this->getProcessName($mfcRequestId)
        );
    }

    /**
     * Имя процесса обновления профиля
     */
    private function getProcessName(int $mfcRequestId): string
    {
        return sprintf('%s_%s', self::PROCESS_NAME, $mfcRequestId);
    }

    /**
     * Отправка уведомления админу о том, что в процессе изменения логина возникли ошибки
     */
    private function sendAlarmToAdmin(string $message): void
    {
        $this->mailer->sendMail(
            Yii::$app->params['mfcEmailList'],
            UserRequestModule::t('common', 'ERROR_REQUEST_SENDING_FAIL_SUBJECT'),
            compact('message'),
            'alarm'
        );
    }

    /**
     * Признак того, что уже можно обратить внимание на эту проблему
     *
     * @param Exception $e
     * @param MfcRequest $mfcRequest
     *
     * @return bool
     */
    private function shouldWePayAttentionTo(Exception $e, MfcRequest $mfcRequest): bool
    {
        return !($e instanceof RemoteServiceException)
            || $mfcRequest->isAttemptsLimitReached();
    }
}
