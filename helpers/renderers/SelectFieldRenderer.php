<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\CustomHtml;

/**
 * Генератор выпадающего списка
 */
class SelectFieldRenderer extends BaseFormFieldRenderer
{
    /**
     * {@inheritdoc}
     */
    public function renderInput(array $specification): string
    {
        if (isset($specification['selection'])) {
            $specification['selection'] = (int)$specification['selection'];
        }

        return CustomHtml::dropDownList(
            $this->getFieldName($specification['name']),
            $specification['selection'] ?? null,
            $this->getItems($specification),
            $this->getOptions($specification)
        );
    }
}
