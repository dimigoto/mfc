<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use DateTime;
use dosamigos\datepicker\DatePicker;
use Exception;

/**
 * Генератор поля ввода даты
 */
class DateFieldRenderer extends BaseFormFieldRenderer
{
    /**
     * {@inheritdoc}
     */
    public function renderInput(array $specification): string
    {
        $options = $this->getOptions($specification);
        $value = $this->getValue($specification);

        $clientOptions = [
            'autoclose' => true,
            'format' => 'dd.mm.yyyy',
            'class' => 'form-control',
        ];

        if (!empty($specification['clientOptions'])) {
            $clientOptions = array_merge($clientOptions, $specification['clientOptions']);
        }

        return DatePicker::widget([
            'name' => $this->getFieldName($specification['name']),
            'value' => $value,
            'template' => '{addon}{input}',
            'language' => 'ru',
            'clientOptions' => $clientOptions,
            'options' => $options,
        ]);
    }

    /**
     * Содержимое поля ввода
     *
     * @param array $specification Спецификация
     *
     * @return string|null
     */
    protected function getValue(array $specification): ?string
    {
        if (isset($specification['value'])) {
            try {
                $date = new DateTime($specification['value']);

                return $date->format('d.m.Y');
            } catch (Exception $e) {
            }
        }

        return null;
    }
}
