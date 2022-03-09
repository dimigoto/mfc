<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use DateTime;
use Exception;
use Yii;

/**
 * Генератор поля ввода даты и времени
 */
class DateTimeFieldRenderer extends TextFieldRenderer
{
    /**
     * Параметры тега поля ввода
     *
     * @param array $specification Спецификация
     * @param array $customRules
     *
     * @return array
     * @throws Exception
     */
    protected function getOptions(array $specification, array $customRules = []): array
    {
        $_customRules = $customRules;
        $_customRules['minute_round'] = 15;

        if (isset($specification['min_hour'])) {
            $_customRules['min_hour'] = $specification['min_hour'];
        }

        if (isset($specification['max_hour'])) {
            $_customRules['max_hour'] = $specification['max_hour'];
        }

        if (isset($specification['only_workdays'])) {
            $_customRules['only_workdays'] = $specification['only_workdays'];

            $celebrations = Yii::$app->holidayManager->getCelebrations();
            $celebrationsAsString = array_map(
                function (DateTime $date) {
                    return $this->getJavascriptDate($date);
                },
                $celebrations
            );
            $_customRules['restricted_dates'] = sprintf(
                '[%s]',
                implode(', ', $celebrationsAsString)
            );
        }

        try {
            $date = new DateTime();

            if (isset($specification['min_workdays_offset'])) {
                $date = Yii::$app->holidayManager->findNthWorkingDay(
                    (int)$specification['min_workdays_offset']
                );
            }

            $_customRules['min'] = $this->getJavascriptDate($date);
        } catch (Exception $e) {
        }

        $_customRules['valid'] = '/^\d{2}\.\d{2}\.\d{4}\s\d{2}\:\d{2}$/i';

        $result = parent::getOptions($specification, $_customRules);

        $cssClass = [$result['class'], 'datetime picker'];

        $result['class'] = implode(' ', $cssClass);

        return $result;
    }

    /**
     * Дата для Javascript
     *
     * @param DateTime $date Дата
     *
     * @return string
     */
    protected function getJavascriptDate(DateTime $date): string
    {
        return sprintf(
            'new Date(%s,%s,%s)',
            $date->format('Y'),
            $date->format('n') - 1,
            $date->format('j')
        );
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
