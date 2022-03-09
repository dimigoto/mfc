<?php

namespace common\modules\mfc\jobs;

use common\jobs\BaseJob;
use common\modules\mfc\factories\MfcRequestSendServiceFactory;
use common\modules\mfc\repositories\MfcRequestRepository;
use common\modules\mfc\services\MfcRequestsBatchSendService;
use common\services\TimerService;
use DateTime;
use Exception;
use Throwable;
use Yii;
use yii\queue\Queue;

/**
 * Отправка заявок "Единого окна" в 1С
 */
class MfcRequestsSendJob extends BaseJob
{
    /**
     * @param Queue $queue which pushed and is handling the job
     *
     * @return void|mixed result of the job execution
     * @throws Exception
     */
    public function execute($queue)
    {
        $timerService = new TimerService();
        $timerService->startTimer();

        try {
            $mfcRequestSendServiceFactory = new MfcRequestSendServiceFactory(
                Yii::$app->dateTimeUtils,
                Yii::$app->soapManager,
                Yii::$app->mailer
            );
            $mfcRequestsBatchSendService = new MfcRequestsBatchSendService(
                new MfcRequestRepository(),
                $mfcRequestSendServiceFactory->createService()
            );
            $mfcRequestsBatchSendService->sendMfcRequests();
        } catch (Throwable $e) {
            $this->log(
                sprintf(
                    '%s Ошибка в процессе отправки заявок "Единого окна" в 1С %s%s',
                    (new DateTime())->format(DATE_W3C),
                    PHP_EOL,
                    $e->getMessage()
                )
            );
        }
    }
}
