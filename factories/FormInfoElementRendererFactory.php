<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\helpers\renderers\AlertInfoRenderer;
use common\modules\mfc\helpers\renderers\InfoRenderer;
use common\modules\mfc\interfaces\FormElementRendererInterface;

/**
 * Фабрика генераторов информационных элементов форм
 */
class FormInfoElementRendererFactory extends BaseFormElementRendererFactory
{
    public const ALERT_TYPE = 'alert-info';

    /**
     * {@inheritdoc}
     */
    public function createRenderer(string $type, FormSchemeHelper $formSchemeHelper): FormElementRendererInterface
    {
        switch ($type) {
            case self::ALERT_TYPE:
                return new AlertInfoRenderer();
            default:
                return new InfoRenderer($this->user);
        }
    }
}
