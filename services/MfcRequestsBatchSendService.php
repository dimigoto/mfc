<?php

declare(strict_types=1);

namespace common\modules\mfc\services;

use common\modules\mfc\repositories\MfcRequestRepository;
use Exception;

/**
 * Сервис по отправке созданных заявок "Единого окна" в 1С
 */
class MfcRequestsBatchSendService
{
    private MfcRequestRepository $mfcRequestRepository;
    private MfcRequestSendService $mfcRequestSendService;

    public function __construct(
        MfcRequestRepository $mfcRequestRepository,
        MfcRequestSendService $mfcRequestSendService
    ) {
        $this->mfcRequestRepository = $mfcRequestRepository;
        $this->mfcRequestSendService = $mfcRequestSendService;
    }

    /**
     * Отправка заявок
     */
    public function sendMfcRequests(): void
    {
        $mfcRequests = $this->mfcRequestRepository->findAllNotSent();

        foreach ($mfcRequests as $mfcRequest) {
            try {
                $this->mfcRequestSendService->sendMfcRequest($mfcRequest);
            } catch (Exception $e) {
            }
        }
    }
}
