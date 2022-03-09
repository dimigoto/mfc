<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\CustomHtml;

/**
 * Генератор группы флаговых кнопок
 */
class CheckboxFieldRenderer extends BaseFormFieldRenderer
{
    protected string $cssClassDefault = 'options';

    /**
     * {@inheritdoc}
     */
    public function getContainerOptions(array $specification): array
    {
        $customRules = [];

        if ($this->formSchemeHelper->isRequired($specification)) {
            $customRules['required'] = '\'[this] === 0\'';
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
        return CustomHtml::checkboxList(
            $this->getFieldName($specification['name']),
            $specification['selection'] ?? null,
            $this->getItems($specification)
        );
    }
}
