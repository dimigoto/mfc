<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererFactoryInterface;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userUniver\models\User;

/**
 * Базовая фабрика генераторов элементов форм
 */
abstract class BaseFormElementRendererFactory implements FormElementRendererFactoryInterface
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
    ): FormElementRendererInterface;
}
