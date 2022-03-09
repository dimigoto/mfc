<?php

namespace common\modules\mfc\interfaces;

/**
 * Интерфейс генератора элемента формы
 */
interface ReaderInterface
{
    /**
     * Чтение сохранённого значения
     *
     * @param mixed $value Сохранённое значение
     * @param array $formElement Спецификация элемента формы
     * @param array $options Дополнительные параметры
     *
     * @return string
     */
    public function readValue($value, array $formElement, array $options): string;
}
