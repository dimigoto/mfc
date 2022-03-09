<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

use common\Helpers\ArrayHelper;
use common\modules\userUniver\services\UserRoleService;
use Yii;

/**
 * Фильтр доступа к сущностям которые имеют определённые роли пользователей на просмотр
 */
class FilterRolesHelper
{
    private UserRoleService $userRoles;

    public function __construct()
    {
        $this->userRoles = new UserRoleService();
    }

    /**
     * @param array $category
     *
     * @return array
     */
    public function filteredSubcategories(array $category): array
    {
        $result = [];

        foreach ($category as $child) {
            if (!empty($child->getRoles())) {
                $userRoles = $this->userRoles->getAllRolesFlatByUser(Yii::$app->user->id);

                $count = 0;
                foreach ($child->getRoles() as $i) {
                    if (ArrayHelper::isIn($i, $userRoles)) {
                        $count++;
                    }
                }

                if ($count === 0) {
                    continue;
                }
            }
            $result[] = $child;
        }

        return $result;
    }
}
