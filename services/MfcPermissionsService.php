<?php

declare(strict_types=1);

namespace common\modules\mfc\services;

use common\modules\userUniver\services\UserRoleService;

/**
 * Сервис для определения разрешений на доступ к элементам "Единого окна"
 */
class MfcPermissionsService
{
    private int $userId;
    private UserRoleService $userRoleService;

    public function __construct(int $userId)
    {
        $this->userRoleService = new UserRoleService();
        $this->userId = $userId;
    }

    /**
     * Роли пользователя
     *
     * @return array
     */
    public function getRoles(): array
    {
        $result = [];

        if ($this->isStudent()) {
            $result[] = UserRoleService::ROLE_STUDENT;
        }

        if ($this->isEmployee()) {
            $result[] = UserRoleService::ROLE_EMPLOYEE;
        }

        if ($this->isTempPassIssuer()) {
            $result[] = UserRoleService::ROLE_TEMP_PASS_ISSUER;
        }

        if ($this->isTesterFrontend()) {
            $result[] = UserRoleService::ROLE_TESTER_FRONTEND;
        }

        if ($this->isFreshman()) {
            $result[] = UserRoleService::ROLE_FRESHMAN;
        }

        if ($this->isPps()) {
            $result[] = UserRoleService::ROLE_PPS;
        }

        return $result;
    }

    /**
     * Проверка, является ли пользователь тестировщиком фронтенда
     *
     * @return bool
     */
    private function isTesterFrontend(): bool
    {
        return $this->userRoleService->isFrontendTesterByLocalRoles($this->userId);
    }

    /**
     * Проверка, является ли пользователь обучающимся
     *
     * @return bool
     */
    private function isStudent(): bool
    {
        return $this->userRoleService->isStudentByLocalRoles($this->userId);
    }

    /**
     * Проверка, является ли пользователь сотрудником
     *
     * @return bool
     */
    private function isEmployee(): bool
    {
        return $this->userRoleService->isEmployeeByLocalRoles($this->userId);
    }

    /**
     * Проверка, является ли пользователь сотрудником
     *
     * @return bool
     */
    private function isTempPassIssuer(): bool
    {
        return $this->userRoleService->isTempPassIssuerByLocalRoles($this->userId);
    }

    /**
     * Проверка, является ли пользователь первокурсником
     *
     * @return bool
     */
    private function isFreshman(): bool
    {
        return $this->userRoleService->isFreshman($this->userId);
    }

    /**
     * Проверка, является ли пользователь первокурсником
     *
     * @return bool
     */
    private function isPps(): bool
    {
        return $this->userRoleService->isPps($this->userId);
    }
}
