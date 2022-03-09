<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\mfc\MfcModule;
use common\modules\userUniver\models\User;
use common\modules\userUniver\services\UserRoleService;
use Exception;

/**
 * Генератор выпадающего списка для выбора категории автомобильного пропуска
 */
class CarPassCategoryFieldRenderer implements FormElementRendererInterface
{
    private const PASS_CATEGORY_BASIC = 'BASIC';
    private const PASS_CATEGORY_SUPER = 'SUPER';
    private const PASS_CATEGORY_PERIOD = 'PERIOD';

    private User $user;
    private UserRoleService $userRoleService;
    private FormSchemeHelper $formSchemeHelper;

    /**
     * @throws Exception
     */
    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;
        $this->user = $user;
        $this->userRoleService = new UserRoleService();
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;

        if ($this->userRoleService->isEmployeeByLocalRoles($this->user->id)) {
            $_specification['itemsAsIs'] = [
                '' => MfcModule::t('common', 'CAR_PASS_CATEGORY_ITEMS_DEFAULT'),
                self::PASS_CATEGORY_BASIC => MfcModule::t('common', 'CAR_PASS_CATEGORY_ITEMS_BASIC'),
                self::PASS_CATEGORY_SUPER => MfcModule::t('common', 'CAR_PASS_CATEGORY_ITEMS_SUPER'),
                self::PASS_CATEGORY_PERIOD => MfcModule::t('common', 'CAR_PASS_CATEGORY_ITEMS_PERIOD'),
            ];

            $fieldRenderer = new SelectFieldRenderer($this->formSchemeHelper);
        } else {
            $fieldRenderer = new HiddenFieldRenderer($this->formSchemeHelper);
            $_specification['value'] = self::PASS_CATEGORY_BASIC;
            $_specification['isHidden'] = true;
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
