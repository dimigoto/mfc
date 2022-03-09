<?php

namespace common\modules\mfc\interfaces;

/**
 * Интерфейс генератора группы полей ввода
 */
interface FormFieldsetRendererInterface
{
    /**
     * Генерация группы полей ввода по спецификации
     *
     * @param array $specification Спецификация
     *
     * @return string
     */
    public function render(array $specification): string;

    /**
     * Специфичный Javascript
     *
     * @return string
     */
    public function getCustomJavascript(): string;

    /**
     * Специфичные правила
     *
     * @return array
     */
    public function getCustomRules(): array;
}
