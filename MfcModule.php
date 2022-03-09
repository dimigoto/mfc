<?php

declare(strict_types=1);

namespace common\modules\mfc;

use common\interfaces\BackendMenuProviderInterface;
use common\modules\BaseModule;
use common\modules\mfc\services\MfcBackendMenuProvider;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;

class MfcModule extends BaseModule implements BootstrapInterface
{
    /**
     * {@inheritDoc}
     */
    public function init(): void
    {
        parent::init();

        $this->registerTranslations([
            $this->getMessagesNamespace() . 'common' => 'common.php',
            $this->getMessagesNamespace() . 'backend' => 'backend.php',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function bootstrap($app): void
    {
        $app->getUrlManager()->addRules(
            $this->importRoutesRules($app->id)
        );
    }

    /**
     * Меню админки
     *
     * @return BackendMenuProviderInterface
     * @throws InvalidConfigException
     */
    public function createBackendMenuProvider(): BackendMenuProviderInterface
    {
        return Yii::createObject(MfcBackendMenuProvider::class);
    }
}
