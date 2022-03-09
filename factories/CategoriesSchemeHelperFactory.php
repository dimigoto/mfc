<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\CategoriesSchemeHelper;
use common\modules\mfc\helpers\ReadSchemeFileHelper;
use common\modules\mfc\interfaces\ReaderSchemeInterface;
use common\modules\mfc\services\MfcPermissionsService;

class CategoriesSchemeHelperFactory
{
    private const FILE_NAME = 'categories';

    private ReadSchemeFileHelper $readSchemeHelper;

    public function __construct(ReaderSchemeInterface $readSchemeHelper)
    {
        $this->readSchemeHelper = $readSchemeHelper;
    }

    /**
     * @param int|null $userId
     * @param bool $withArchive
     *
     * @return CategoriesSchemeHelper
     */
    public function createObject(?int $userId, bool $withArchive): CategoriesSchemeHelper
    {
        $structure = $this->readSchemeHelper->readScheme(self::FILE_NAME);

        $roles = [];

        $withRoles = null !== $userId;

        if ($withRoles) {
            $mfcPermissionsService = new MfcPermissionsService($userId);
            $roles = $mfcPermissionsService->getRoles();
        }

        return new CategoriesSchemeHelper($roles, $structure, $withArchive, $withRoles);
    }
}
