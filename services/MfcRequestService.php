<?php

declare(strict_types=1);

namespace common\modules\mfc\services;

use common\exceptions\NotFoundException;
use common\exceptions\DatabaseException;
use common\modules\mfc\models\MfcRequest;
use common\modules\mfc\repositories\MfcRequestRepository;
use common\modules\userRequest\repositories\UserRequestRepository;
use Exception;
use Throwable;

/**
 * Сервис по управлению заявками ИТИЛ
 */
class MfcRequestService
{
    private UserRequestRepository $userRequestRepository;
    private MfcRequestRepository $mfcRequestRepository;

    public function __construct()
    {
        $this->userRequestRepository = new UserRequestRepository();
        $this->mfcRequestRepository = new MfcRequestRepository();
    }

    /**
     * Создание заявки
     *
     * @param MfcRequest $mfcRequest
     *
     * @return MfcRequest
     * @throws NotFoundException
     * @throws Throwable
     */
    public function saveMfcRequest(MfcRequest $mfcRequest): MfcRequest
    {
        $id = $this->mfcRequestRepository->save($mfcRequest);

        $userRequest = $this->userRequestRepository->findOneById($id);

        if ($userRequest instanceof MfcRequest) {
            return $userRequest;
        }

        throw new Exception('Ошибка при создании заявки');
    }

    /**
     * Обновление заявки
     *
     * @param MfcRequest $mfcRequest
     *
     * @throws NotFoundException
     * @throws DatabaseException
     */
    public function updateMfcRequest(MfcRequest $mfcRequest): void
    {
        $this->mfcRequestRepository->update($mfcRequest);
    }

    /**
     * Удаление заявки
     *
     * @param MfcRequest $mfcRequest
     */
    public function deleteMfcRequest(MfcRequest $mfcRequest): void
    {
        try {
            $this->userRequestRepository->delete($mfcRequest);
        } catch (NotFoundException $e) {
        }
    }
}
