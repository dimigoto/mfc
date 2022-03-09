<?php

declare(strict_types=1);

namespace common\modules\mfc\controllers\frontend;

use common\exceptions\NotFoundException;
use common\modules\mfc\factories\MfcElementsRepositoryFactory;
use common\modules\mfc\factories\MfcRequestFactory;
use common\modules\mfc\helpers\FormContentRenderHelper;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\MfcModule;
use common\modules\mfc\models\MfcRequest;
use common\modules\mfc\repositories\MfcElementsRepository;
use common\modules\mfc\services\MfcRequestDataService;
use common\modules\mfc\services\MfcRequestService;
use common\modules\settle\factories\SettleUserServiceFactory;
use common\modules\upload\services\UploadService;
use common\modules\userRequest\repositories\UserRequestRepository;
use common\modules\userRequest\UserRequestModule;
use common\modules\userUniver\models\User;
use Exception;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Заявки "Единого окна"
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'category' => ['GET'],
                    'download-file' => ['GET'],
                    'enquiry' => ['GET'],
                    'file' => ['GET'],
                    'index' => ['GET'],
                    'save-request' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Список категорий
     */
    public function actionIndex(): string
    {
        $user = $this->getUser();

        $mfcElementsRepositoryFactory = new MfcElementsRepositoryFactory();
        $mfcElementsRepository = $mfcElementsRepositoryFactory->createRepository($user->id, false);
        $items = $mfcElementsRepository->findAllForCategory();

        $settleUserServiceFactory = new SettleUserServiceFactory(Yii::$app->dateTimeUtils);
        $settleUserService = $settleUserServiceFactory->createService($user);

        return $this->render(
            'index',
            compact(
                'items',
                'settleUserService'
            )
        );
    }

    /**
     * Карта услуг
     */
    public function actionMap()
    {
        $user = $this->getUser();

        $mfcElementsRepositoryFactory = new MfcElementsRepositoryFactory();
        $mfcElementsRepository = $mfcElementsRepositoryFactory->createRepository($user->id, false);

        $tree = $mfcElementsRepository->findTreeOfCategoriesAndElements();

        $settleUserServiceFactory = new SettleUserServiceFactory(Yii::$app->dateTimeUtils);
        $settleUserService = $settleUserServiceFactory->createService($user);

        return $this->render(
            'map',
            compact(
                'tree'
            )
        );
    }

    /**
     * Список видов услуг
     *
     * @param string $id ID категории услуг
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionCategory(string $id): string
    {
        $user = $this->getUser();

        $mfcElementsRepositoryFactory = new MfcElementsRepositoryFactory();
        $mfcElementsRepository = $mfcElementsRepositoryFactory->createRepository($user->id, false);
        $category = $mfcElementsRepository->findOneCategoryById($id);

        if ($category === null) {
            throw new NotFoundHttpException(
                MfcModule::t('common', 'ERROR_CATEGORY_NOT_FOUND')
            );
        }

        $categoryId = $category->getId();
        $categoryName = $category->getTitle();
        $items = $mfcElementsRepository->findAllForCategory($categoryId);

        if (empty($items)) {
            throw new NotFoundHttpException(
                MfcModule::t('common', 'ERROR_CATEGORY_NOT_FOUND')
            );
        }

        $parents = $mfcElementsRepository->findParentCategoriesByCategoryId($category->getId());

        return $this->render(
            'category',
            compact('categoryId', 'categoryName', 'items', 'parents')
        );
    }

    /**
     * Заказ услуги
     *
     * @param string $id ID услуги
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEnquiry(string $id): string
    {
        $user = $this->getUser();

        $mfcElementsRepositoryFactory = new MfcElementsRepositoryFactory();
        $mfcElementsRepository = $mfcElementsRepositoryFactory->createRepository($user->id, false);

        $enquiryType = $mfcElementsRepository->findOneEnquiryById($id);

        if (!$enquiryType) {
            throw new NotFoundHttpException(
                MfcModule::t('common', 'ERROR_ENQUIRY_TYPE_NOT_FOUND')
            );
        }

        $parents = $mfcElementsRepository->findParentCategoriesByEnquiryTypeId($enquiryType->getId());

        $formSchemeHelper = new FormSchemeHelper($enquiryType->getStructure());

        $formRenderHelper = new FormContentRenderHelper(
            $this->getUser(),
            $formSchemeHelper
        );

        return $this->render(
            'enquiryType',
            compact('enquiryType', 'parents', 'formRenderHelper')
        );
    }

    /**
     * Скачивание файла с бланком заявления
     *
     * @param string $id ID услуги
     * @param string $fileId ID файла бланка
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDownloadFile(string $id, string $fileId): Response
    {
        $user = $this->getUser();

        $mfcElementsRepositoryFactory = new MfcElementsRepositoryFactory();
        $mfcElementsRepository = $mfcElementsRepositoryFactory->createRepository($user->id, false);

        $enquiryType = $mfcElementsRepository->findOneEnquiryById($id);

        if (!$enquiryType) {
            throw new NotFoundHttpException(
                MfcModule::t('common', 'ERROR_ENQUIRY_TYPE_NOT_FOUND')
            );
        }

        $filePath = $enquiryType->getFilePath($fileId);

        if (empty($filePath)) {
            throw new NotFoundHttpException(
                MfcModule::t('common', 'ERROR_ENQUIRY_TYPE_FILE_NOT_FOUND')
            );
        }

        return Yii::$app->response->sendFile(
            $filePath
        );
    }

    /**
     * Сохранение заявки
     */
    public function actionSaveRequest(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $rawData = Yii::$app->request->post();

        $enquiryTypeId = $rawData['Enquiry']['type'];

        $user = $this->getUser();

        $mfcElementsRepositoryFactory = new MfcElementsRepositoryFactory();
        $mfcElementsRepository = $mfcElementsRepositoryFactory->createRepository($user->id, false);

        $mfcEnquiryType = $mfcElementsRepository->findOneEnquiryById($enquiryTypeId);

        if (!$mfcEnquiryType) {
            return [
                'success' => false,
                'message' => MfcModule::t('common', 'ERROR_ENQUIRY_TYPE_NOT_FOUND'),
            ];
        }

        if (!$mfcEnquiryType->canSaveRequestByRateLimit($user)) {
            $timeToWaitDisplay = $mfcEnquiryType->getTimeToWaitDisplay(
                $this->getUser()
            );

            if (empty($timeToWaitDisplay)) {
                return [
                    'success' => false,
                    'message' => MfcModule::t(
                        'common',
                        'ERROR_ENQUIRY_TYPE_RATE_LIMIT_TRY_AGAIN'
                    ),
                ];
            }

            return [
                'success' => false,
                'message' => MfcModule::t(
                    'common',
                    'ERROR_ENQUIRY_TYPE_RATE_LIMIT',
                    ['next_time' => $timeToWaitDisplay]
                ),
            ];
        }

        try {
            $mfcRequestDataService = new MfcRequestDataService(
                $mfcEnquiryType,
                $rawData['Enquiry']
            );
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $mfcRequestService = new MfcRequestService();

        try {
            $data = $mfcRequestDataService->getFilteredData();
            $mfcRequestFactory = new MfcRequestFactory();
            $mfcRequest = $mfcRequestFactory->createMfcRequest(
                $enquiryTypeId,
                $mfcRequestDataService->getFilteredDataForSave(),
                $data['phone'] ?? '',
                $data['comment'] ?? '',
                $user->id
            );

            $mfcRequest = $mfcRequestService->saveMfcRequest($mfcRequest);
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);

            $mfcRequestDataService->deleteUploadedFiles();

            if (isset($mfcRequest)) {
                $mfcRequestService->deleteMfcRequest($mfcRequest);
            }

            return [
                'success' => false,
                'message' => MfcModule::t(
                    'common',
                    'ERROR_ENQUIRY_NOT_SAVED',
                    ['url_support' => Yii::$app->params['url.support']]
                ),
            ];
        }

        return [
            'success' => true,
            //'message' => 'Заявка отправлена. Содержимое запроса:' . $message,
            'message' => MfcModule::t(
                'common',
                'MESSAGE_REQUEST_SENT',
                [
                    'datetime' => $mfcRequest->createdAtDisplay,
                    'url_support' => Yii::$app->params['url.support'],
                ]
            ),
        ];
    }

    /**
     * Файл, вложенный в заявку
     *
     * @param int $requestId ID заявки
     * @param string $fileId ID файла
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionFile(int $requestId, string $fileId): Response
    {
        try {
            $userRequestRepository = new UserRequestRepository();
            $userRequest = $userRequestRepository->findOneByIdForUser(
                $this->getUser()->id,
                $requestId
            );

            if (
                !($userRequest instanceof MfcRequest)
                || !$userRequest->hasFile($fileId)
            ) {
                throw new NotFoundException(
                    UserRequestModule::t('common', 'ERROR_REQUEST_NOT_FOUND')
                );
            }
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $fileInfo = $userRequest->getFileInfo($fileId);

        $uploadService = new UploadService();

        return Yii::$app->response->sendFile(
            $uploadService->getAbsolutePath($fileInfo['path'])
        );
    }

    /**
     * Пользователь
     *
     * @return User
     */
    private function getUser(): User
    {
        return Yii::$app->user->identity;
    }
}
