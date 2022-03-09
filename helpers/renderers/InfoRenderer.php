<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\mfc\MfcModule;
use common\modules\userUniver\models\User;
use yii\helpers\Html;

/**
 * Генератор информационного элемента
 */
class InfoRenderer implements FormElementRendererInterface
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        return Html::tag(
            'div',
            MfcModule::t('common', $specification['text']),
            []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomJavascript(): string
    {
        return '';
    }
}
