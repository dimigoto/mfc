<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\interfaces\FormFieldsetRendererInterface;

/**
 * Генератор группы элементов формы
 */
abstract class BaseFormFieldsetRenderer implements FormFieldsetRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        return '';
    }

    /**
     * Специфичный Javascript
     *
     * @return string
     */
    public function getCustomJavascript(): string
    {
        return '';
    }

    /**
     * Специфичные правила
     *
     * @return array
     */
    public function getCustomRules(): array
    {
        return [];
    }
}
