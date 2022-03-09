<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userUniver\models\User;
use Yii;

/**
 * Генератор поля ввода даты рождения
 */
class BirthdayFieldRenderer implements FormElementRendererInterface
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
        $birthday = $this->user->userProfile->birthday;

        if ($birthday) {
            $fieldRenderer = new TextFieldRenderer($this->formSchemeHelper);
            $_specification['value'] = Yii::$app->formatter->format($birthday, 'date');
            $_specification['readonly'] = true;
        } else {
            $fieldRenderer = new DateFieldRenderer($this->formSchemeHelper);
            $_specification['readonly'] = false;
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
