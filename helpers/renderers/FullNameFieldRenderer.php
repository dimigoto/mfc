<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userUniver\models\User;

/**
 * Генератор поля ввода Ф.И.О.
 */
class FullNameFieldRenderer implements FormElementRendererInterface
{
    private User $user;
    private FormSchemeHelper $formSchemeHelper;

    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->user = $user;
        $this->formSchemeHelper = $formSchemeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $_specification['value'] = $this->user->userProfile->fullNameDisplay;

        $fieldRenderer = new TextFieldRenderer($this->formSchemeHelper);

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
