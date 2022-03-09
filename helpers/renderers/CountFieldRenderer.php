<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;

/**
 * Генератор выпадающего списка для выбора количества
 */
class CountFieldRenderer implements FormElementRendererInterface
{
    private FormSchemeHelper $formSchemeHelper;

    public function __construct(FormSchemeHelper $formSchemeHelper)
    {
        $this->formSchemeHelper = $formSchemeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $_specification['itemsAsIs'] = [];

        for ($counter = 1; $counter <= 5; $counter++) {
            $_specification['itemsAsIs'][$counter] = $counter;
        }

        $fieldRenderer = new SelectFieldRenderer($this->formSchemeHelper);

        return $fieldRenderer->render($_specification);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomJavascript(): string
    {
        return '';
    }
}
