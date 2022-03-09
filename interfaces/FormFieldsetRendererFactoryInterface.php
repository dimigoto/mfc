<?php

namespace common\modules\mfc\interfaces;

use common\modules\mfc\helpers\FormSchemeHelper;

/**
 * Интерфейс фабрики генераторов групп полей ввода
 */
interface FormFieldsetRendererFactoryInterface
{
    /**
     * Создание визуализатора элемента формы
     *
     * @param string $type Тип элемента
     * @param FormSchemeHelper $formSchemeHelper
     *
     * @return FormFieldsetRendererInterface
     */
    public function createRenderer(string $type, FormSchemeHelper $formSchemeHelper): FormFieldsetRendererInterface;
}
