<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\Helpers\ArrayHelper;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\settle\factories\AccommodationServiceFactory;
use common\modules\settle\interfaces\AccommodationServiceInterface;
use common\modules\userUniver\models\User;
use common\repositories\DictionaryBuildingRepository;

/**
 * Генератор выпадающего списка с множественным выбором для выбора здания
 */
class BuildingsFieldRenderer implements FormElementRendererInterface
{
    private FormSchemeHelper $formSchemeHelper;
    private AccommodationServiceInterface $accommodationService;
    private DictionaryBuildingRepository $dictionaryBuildingRepository;

    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;

        $accommodationServiceFactory = new AccommodationServiceFactory();
        $this->accommodationService = $accommodationServiceFactory->createService($user);

        $this->dictionaryBuildingRepository = new DictionaryBuildingRepository();
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $_specification['itemsAsIs'] = $this->getBuildings();

        $fieldRenderer = new MultiselectFieldRenderer($this->formSchemeHelper);

        return $fieldRenderer->render($_specification);
    }

    /**
     * Здания
     *
     * @return array
     */
    private function getBuildings(): array
    {
        $educationalBuildings = $this->dictionaryBuildingRepository->getAllActiveNamedByLetterAsArray();

        $result = ArrayHelper::map($educationalBuildings, 'name', 'name');

        $dormitories = $this->accommodationService->getAccommodationData();

        $result = array_merge(
            $result,
            ArrayHelper::map($dormitories[1], 'name', 'name')
        );

        $result = array_merge(
            $result,
            ArrayHelper::map($dormitories[2], 'name', 'name')
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomJavascript(): string
    {
        return '';
    }
}
