<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;

/**
 * Генератор группы поле ввода интервала лет
 */
class YearsIntervalFieldRenderer implements FormElementRendererInterface
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
        $yearCurrent = date('Y');
        $years = range($yearCurrent, 1900);
        $items = [];

        foreach ($years as $year) {
            $items[$year] = $year;
        }

        $_specification = $specification;
        $_specification['itemsAsIs'] = $items;
        $_specification['selection'] = $yearCurrent;

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
