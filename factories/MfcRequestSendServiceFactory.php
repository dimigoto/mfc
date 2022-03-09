<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\components\dateTimeUtils\interfaces\DateTimeUtilsInterface;
use common\components\Mailer;
use common\components\SoapManager;
use common\modules\mfc\services\MfcRequestSendService;
use common\modules\mfc\services\MfcRequestService;
use common\modules\upload\services\UploadService;
use common\modules\userNsi\repositories\UserNsiRepository;
use common\modules\userRequest\services\LogUserRequestService;
use common\modules\userUniver\repositories\UserProfileINNRepository;
use common\modules\userUniver\repositories\UserProfileRepository;
use common\modules\userUniver\services\UserProfileINNService;
use common\services\LockService;

class MfcRequestSendServiceFactory
{
    private DateTimeUtilsInterface $dateTimeUtils;
    private SoapManager $soapManager;
    private Mailer $mailer;

    public function __construct(
        DateTimeUtilsInterface $dateTimeUtils,
        SoapManager $soapManager,
        Mailer $mailer
    ) {
        $this->dateTimeUtils = $dateTimeUtils;
        $this->soapManager = $soapManager;
        $this->mailer = $mailer;
    }

    public function createService(): MfcRequestSendService
    {
        return new MfcRequestSendService(
            $this->dateTimeUtils,
            $this->soapManager,
            $this->mailer,
            new UserProfileINNService(
                new UserProfileINNRepository(),
                new UserNsiRepository(),
                new UserProfileRepository()
            ),
            new UploadService(),
            new MfcRequestService(),
            new LockService(),
            new LogUserRequestService()
        );
    }
}
