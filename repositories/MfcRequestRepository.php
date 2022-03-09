<?php

declare(strict_types=1);

namespace common\modules\mfc\repositories;

use common\exceptions\NotFoundException;
use common\exceptions\DatabaseException;
use common\Helpers\JsonHelper;
use common\modules\mfc\MfcModule;
use common\modules\mfc\models\MfcRequest;
use common\modules\mfc\models\records\MfcRequestRecord;
use common\modules\userRequest\models\records\UserRequestRecord;
use common\modules\userRequest\models\UserRequest;
use common\modules\userRequest\repositories\UserRequestRepository;
use common\modules\userRequest\services\UserRequestService;
use common\modules\userUniver\models\User;
use Throwable;

/**
 * Коллекция заявок модуля "Единое окно"
 */
class MfcRequestRepository extends UserRequestRepository
{
    /**
     * Крайняя для пользователя заявка заданного типа
     *
     * @param User $user Пользователь
     * @param string $subtype Тип заявки
     *
     * @return UserRequest
     * @throws NotFoundException
     */
    public function findOneRecentForUserBySubtype(User $user, string $subtype): UserRequest
    {
        $result = $this->findAllByType(
            sprintf('%s:%s', MfcRequest::TYPE, $subtype),
            [
                'AND',
                ['NOT IN', 'status', [UserRequest::STATUS_ERROR]],
                ['user_id' => $user->id],
            ],
            1
        );

        if (empty($result)) {
            throw new NotFoundException(
                MfcModule::t('common', 'ERROR_ENQUIRY_NOT_FOUND')
            );
        }

        return $result[0];
    }

    /**
     * Все неотправленные заявки
     *
     * @return MfcRequest[]
     */
    public function findAllNotSent(): array
    {
        return $this->findAllNotSentByType(MfcRequest::TYPE);
    }

    /**
     * Все неотправленные заявки, по которым превышено кол-во попыток отправки
     *
     * @return MfcRequest[]
     */
    public function findAllStalled(): array
    {
        return $this->findAllStalledByType(MfcRequest::TYPE);
    }

    /**
     * Сохранение новой заявки
     *
     * @param MfcRequest $mfcRequest
     *
     * @return int
     * @throws Throwable
     */
    public function save(MfcRequest $mfcRequest): int
    {
        $customRequestRecord = new MfcRequestRecord();
        $customRequestRecord->subtype = $mfcRequest->subtype;
        $customRequestRecord->data = $mfcRequest->data;

        $userRequestService = new UserRequestService($mfcRequest->user);
        $userRequestRecord = $userRequestService->saveUserRequest(
            $mfcRequest->type,
            $mfcRequest->phone,
            $mfcRequest->comment,
            $customRequestRecord
        );

        return $userRequestRecord->id;
    }

    /**
     * Обновление заявки
     *
     * @param MfcRequest $mfcRequest
     *
     * @throws NotFoundException
     * @throws DatabaseException
     */
    public function update(MfcRequest $mfcRequest): void
    {
        $customRequestRecord = MfcRequestRecord::find()
            ->where(['request_id' => $mfcRequest->id])
            ->one();

        if (!$customRequestRecord) {
            throw new NotFoundException(
                MfcModule::t('common', 'ERROR_ENQUIRY_NOT_FOUND')
            );
        }

        $userRequestRecord = UserRequestRecord::find()
            ->where(['id' => $mfcRequest->id])
            ->one();

        if (!$userRequestRecord) {
            throw new NotFoundException(
                MfcModule::t('common', 'ERROR_ENQUIRY_NOT_FOUND')
            );
        }

        $customRequestRecord->subtype = $mfcRequest->subtype;
        $customRequestRecord->data = $mfcRequest->data;

        if (!$customRequestRecord->save()) {
            throw new DatabaseException(
                MfcModule::t(
                    'common',
                    'ERROR_ENQUIRY_NOT_UPDATED',
                    [
                        'id' => $mfcRequest->id,
                        'params' => JsonHelper::jsonEncodePrettyPrint([
                            'subtype' => $mfcRequest->subtype,
                            'data' => $mfcRequest->data,
                        ]),
                        'message' => JsonHelper::jsonEncodePrettyPrint(
                            $customRequestRecord->getFirstErrors()
                        ),
                    ]
                )
            );
        }

        $userRequestRecord->number_foreign = $mfcRequest->number_foreign;
        $userRequestRecord->phone = $mfcRequest->phone;
        $userRequestRecord->comment = $mfcRequest->comment;
        $userRequestRecord->status = $mfcRequest->status;
        $userRequestRecord->answer = $mfcRequest->answer;
        $userRequestRecord->created_foreign = $mfcRequest->created_foreign;
        $userRequestRecord->attempts = $mfcRequest->attempts;

        if (!$userRequestRecord->save()) {
            throw new DatabaseException(
                MfcModule::t(
                    'common',
                    'ERROR_ENQUIRY_NOT_UPDATED',
                    [
                        'id' => $mfcRequest->id,
                        'params' => JsonHelper::jsonEncodePrettyPrint([
                            'number_foreign' => $mfcRequest->number_foreign,
                            'phone' => $mfcRequest->phone,
                            'comment' => $mfcRequest->comment,
                            'status' => $mfcRequest->status,
                            'answer' => $mfcRequest->answer,
                            'created_foreign' => $mfcRequest->created_foreign,
                            'attempts' => $mfcRequest->attempts,
                        ]),
                        'message' => JsonHelper::jsonEncodePrettyPrint(
                            $customRequestRecord->getFirstErrors()
                        ),
                    ]
                )
            );
        }
    }
}
