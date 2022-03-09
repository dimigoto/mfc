<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\mfc\MfcModule;
use yii\bootstrap4\Html;

/**
 * Генератор заголовка внутри формы
 */
class HeadingRenderer implements FormElementRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        return Html::tag(
            $specification['type'],
            MfcModule::t('common', $specification['text'])
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
