<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\exceptions\NotFoundException;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userNsi\repositories\EducationDocumentRepository;
use common\modules\userNsi\repositories\UserNsiRepository;
use common\modules\userUniver\models\User;
use Exception;

/**
 * Генератор выпадающего списка для выбора документа о предыдущем образовании
 */
class PreviousEducationDocumentFieldRenderer implements FormElementRendererInterface
{
    private FormSchemeHelper $formSchemeHelper;
    private array $educationDocuments = [];

    /**
     * @throws Exception
     */
    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;

        try {
            $userNsiRepository = new UserNsiRepository();
            $userNsi = $userNsiRepository->findOneByUsername($user->username);

            $educationDocumentRepository = new EducationDocumentRepository();
            $educationDocuments = $educationDocumentRepository->findAllForUser($userNsi);

            foreach ($educationDocuments as $educationDocument) {
                $this->educationDocuments[$educationDocument->name] = $educationDocument->name;
            }
        } catch (NotFoundException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;

        if (empty($this->educationDocuments)) {
            $fieldRenderer = new TextFieldRenderer($this->formSchemeHelper);
        } else {
            $_specification['itemsAsIs'] = $this->educationDocuments;

            $fieldRenderer = new SelectFieldRenderer($this->formSchemeHelper);
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
