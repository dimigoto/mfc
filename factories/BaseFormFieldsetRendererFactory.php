<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormFieldsetRendererFactoryInterface;
use common\modules\mfc\interfaces\FormFieldsetRendererInterface;
use common\modules\userUniver\models\User;

/**
 * Базовая фабрика генераторов групп полей ввода
 */
abstract class BaseFormFieldsetRendererFactory implements FormFieldsetRendererFactoryInterface
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function createRenderer(
        string $type,
        FormSchemeHelper $formSchemeHelper
    ): FormFieldsetRendererInterface;
}
