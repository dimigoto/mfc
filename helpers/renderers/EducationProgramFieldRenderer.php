<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userUniver\models\User;
use common\modules\userUniver\repositories\StudentProfileRepository;

/**
 * Генератор выпадающего списка для выбора образовательной программы
 */
class EducationProgramFieldRenderer implements FormElementRendererInterface
{
    private FormSchemeHelper $formSchemeHelper;
    private array $educationPrograms = [];

    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;
        $studentProfileRepository = new StudentProfileRepository();
        $studentProfiles = $studentProfileRepository->findAllByUserId($user->id);

        foreach ($studentProfiles as $studentProfile) {
            $educationProgram = $studentProfile->educationProgram;

            if (!$educationProgram) {
                continue;
            }

            $this->educationPrograms[$educationProgram->name] = $educationProgram->name;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $_specification['itemsAsIs'] = $this->educationPrograms;

        $fieldRenderer = new SelectFieldRenderer($this->formSchemeHelper);

        return $fieldRenderer->render($_specification);

        /*return MultipleInput::widget([
            'name' => sprintf('Enquiry[%s]', $specification['name']),
            //'name' => $specification['name'],
            'columns' => [
                [
                    'name' => 'guid',
                    'type'  => 'dropDownList',
                    'defaultValue' => $specification['selection'] ?? null,
                    'items' => ArrayHelper::map($educationPrograms, 'guid', 'name')
                ],
            ],
            'allowEmptyList'    => false,
            'enableGuessTitle'  => false,
            'addButtonPosition' => MultipleInput::POS_ROW,
            //'iconSource' => MultipleInput::ICONS_SOURCE_FONTAWESOME,
        ]);*/
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomJavascript(): string
    {
        return '';
    }
}
