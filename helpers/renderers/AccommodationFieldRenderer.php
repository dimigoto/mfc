<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\settle\factories\AccommodationServiceFactory;
use common\modules\settle\interfaces\AccommodationServiceInterface;
use common\modules\userUniver\models\User;

/**
 * Генератор поля ввода данных о проживании пользователя
 */
class AccommodationFieldRenderer implements FormElementRendererInterface
{
    private FormSchemeHelper $formSchemeHelper;
    private AccommodationServiceInterface $accommodationService;

    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;

        $accommodationServiceFactory = new AccommodationServiceFactory();
        $this->accommodationService = $accommodationServiceFactory->createService($user);
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $userAccommodationData = $this->accommodationService->getUserAccommodationData();

        $fieldRenderer = new TextFieldRenderer($this->formSchemeHelper);

        if ($userAccommodationData) {
            $_specification['value'] = $userAccommodationData['address'];
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
