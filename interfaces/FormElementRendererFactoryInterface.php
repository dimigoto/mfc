<?php

namespace common\modules\mfc\interfaces;

use common\modules\mfc\helpers\FormSchemeHelper;
use Exception;

/**
 * Интерфейс фабрики генераторов элементов формы
 */
interface FormElementRendererFactoryInterface
{
    /**
     * Создание визуализатора элемента формы
     *
     * @param string $type Тип элемента
     * @param FormSchemeHelper $formSchemeHelper
     *
     * @return FormElementRendererInterface
     * @throws Exception
     */
    public function createRenderer(string $type, FormSchemeHelper $formSchemeHelper): FormElementRendererInterface;
}
