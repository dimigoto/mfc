<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\CustomHtml;

/**
 * Генератор группы радио-кнопок
 */
class RadioFieldRenderer extends BaseFormFieldRenderer
{
    protected string $cssClassDefault = 'options';

    /**
     * {@inheritdoc}
     */
    public function getContainerOptions(array $specification): array
    {
        $customRules = [];

        if ($this->formSchemeHelper->isRequired($specification)) {
            $customRules['required'] = '\'[this] != 1\'';
        }

        $result = $this->getOptions($specification, $customRules);

        if (isset($result['required'])) {
            unset($result['required']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function renderInput(array $specification): string
    {
        return CustomHtml::radioList(
            $this->getFieldName($specification['name']),
            $specification['selection'] ?? null,
            $this->getItems($specification)
        );
    }
}
