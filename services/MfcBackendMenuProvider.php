<?php

declare(strict_types=1);

namespace common\modules\mfc\services;

use common\interfaces\BackendMenuProviderInterface;
use common\modules\mfc\MfcModule;
use common\modules\userUniver\services\UserRoleService;

/**
 * Меню для админки
 */
class MfcBackendMenuProvider implements BackendMenuProviderInterface
{
    private UserRoleService $userRoleService;

    public function __construct(UserRoleService $userRoleService)
    {
        $this->userRoleService = $userRoleService;
    }

    public function getMenu(int $userId): array
    {
        $result = [];

        if ($this->userRoleService->isAdmin($userId)) {
            $result[] = [
                'label' => MfcModule::t('backend', 'MFC_SECTION_TITLE'),
                'icon' => 'window-maximize',
                'items' => [
                    [
                        'label' => MfcModule::t('backend', 'MFC_STALLED_SECTION_TITLE'),
                        'icon' => 'window-close',
                        'url' => ['/mfc'],
                    ],
                    [
                        'label' => MfcModule::t('backend', 'MFC_RESEND_SECTION_TITLE'),
                        'icon' => 'cogs',
                        'url' => ['/mfc/default/resend'],
                    ],
                ],
            ];
        }

        return $result;
    }
}
