<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\mfc\MfcModule;
use yii\helpers\Html;

/**
 * Генератор информационного элемента
 */
class AlertInfoRenderer implements FormElementRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        return Html::tag(
            'div',
            MfcModule::t('common', $specification['text']),
            ['class' => 'alert alert-info']
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
