<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\exceptions\NotFoundException;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userUniver\models\User;
use common\modules\userUniver\models\UserProfileContact;
use common\modules\userUniver\repositories\UserProfileContactRepository;

/**
 * Генератор поля ввода адреса регистрации
 */
class AddressFieldRenderer implements FormElementRendererInterface
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
        $contactRepository = new UserProfileContactRepository();

        try {
            $address = $contactRepository->findOneByTypeAndUserProfileId(
                $this->user->userProfile->id,
                UserProfileContact::TYPE_REGISTRATION_ADDRESS
            );
            $_specification['value'] = $address;
        } catch (NotFoundException $e) {
        }

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
