<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userUniver\models\User;

/**
 * Генератор поля ввода контактного телефона
 */
class PhoneFieldRenderer implements FormElementRendererInterface
{
    private FormSchemeHelper $formSchemeHelper;
    private ?string $phone = null;

    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;

        if ($user->userProfile) {
            $this->phone = $user->userProfile->mobile;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;

        $fieldRenderer = new TextFieldRenderer($this->formSchemeHelper);

        if ($this->phone) {
            $_specification['value'] = $this->phone;
            $_specification['readonly'] = true;
        }

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
