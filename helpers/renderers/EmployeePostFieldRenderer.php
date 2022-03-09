<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\userUniver\models\User;
use common\modules\userUniver\repositories\EmployeeProfileRepository;

/**
 * Генератор выпадающего списка для выбора должности сотрудника
 */
class EmployeePostFieldRenderer implements FormElementRendererInterface
{
    private FormSchemeHelper $formSchemeHelper;
    private array $employeePosts = [];

    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;
        $employeeProfileRepository = new EmployeeProfileRepository();
        $employeeProfiles = $employeeProfileRepository->findAllByUserId($user->id);

        foreach ($employeeProfiles as $employeeProfile) {
            $employeePost = $employeeProfile->employeePost;

            if (!$employeePost) {
                continue;
            }

            $this->employeePosts[$employeePost->name] = $employeePost->name;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $_specification['itemsAsIs'] = $this->employeePosts;

        $fieldRenderer = new SelectFieldRenderer($this->formSchemeHelper);

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
