<?php

namespace common\modules\mfc\interfaces;

/**
 * Интерфейс генератора элемента формы
 */
interface FormElementRendererInterface
{
    /**
     * Генерация элемента формы по спецификации
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
}
