<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\CustomHtml;

/**
 * Генератор текстового поля ввода
 */
class TextFieldRenderer extends BaseFormFieldRenderer
{
    /**
     * {@inheritdoc}
     */
    public function renderInput(array $specification): string
    {
        /*if (isset($specification['multiple']) && true === $specification['multiple']) {
            return MultipleInput::widget([
                'name' => $specification['name'],
                'max'               => 6,
                'min'               => 1,
                'allowEmptyList'    => false,
                'enableGuessTitle'  => false,
                'addButtonPosition' => MultipleInput::POS_ROW,
                //'iconSource' => MultipleInput::ICONS_SOURCE_FONTAWESOME,
            ]);
        }*/

        return CustomHtml::textInput(
            $this->getFieldName($specification['name']),
            $specification['value'] ?? null,
            $this->getOptions($specification)
        );
    }
}
