<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\CustomHtml;

/**
 * Генератор текстовой области
 */
class TextareaFieldRenderer extends BaseFormFieldRenderer
{
    /**
     * {@inheritdoc}
     */
    public function renderInput(array $specification): string
    {
        return CustomHtml::textArea(
            $this->getFieldName($specification['name']),
            $specification['value'] ?? null,
            $this->getOptions($specification)
        );
    }
}
