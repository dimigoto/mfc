<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\exceptions\NotFoundException;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userNsi\repositories\UserNsiRepository;
use common\modules\userUniver\models\User;
use common\modules\userUniver\repositories\UserProfilePassportRepository;
use common\modules\userUniver\repositories\UserProfileRepository;
use common\modules\userUniver\services\UserProfilePassportService;
use Exception;

/**
 * Генератор поля ввода паспорта
 */
class PassportFieldRenderer implements FormElementRendererInterface
{
    private FormSchemeHelper $formSchemeHelper;
    private ?string $passport = null;

    /**
     * @throws Exception
     */
    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;

        try {
            $userProfilePassportService = new UserProfilePassportService(
                new UserProfilePassportRepository(),
                new UserNsiRepository(),
                new UserProfileRepository()
            );
            $passport = $userProfilePassportService->findOneByUserProfileId($user->userProfile->id);
            $this->passport = $passport->allAsString;
        } catch (NotFoundException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;

        $fieldRenderer = new TextFieldRenderer($this->formSchemeHelper);

        if ($this->passport) {
            $_specification['value'] = $this->passport;
            $_specification['readonly'] = true;
        } else {
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
