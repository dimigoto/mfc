<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\helpers\renderers\HeadingRenderer;
use common\modules\mfc\interfaces\FormElementRendererInterface;

/**
 * Фабрика генераторов заголовков внутри форм
 */
class FormHeadingElementRendererFactory extends BaseFormElementRendererFactory
{
    /**
     * {@inheritdoc}
     */
    public function createRenderer(string $type, FormSchemeHelper $formSchemeHelper): FormElementRendererInterface
    {
        return new HeadingRenderer();
    }
}
