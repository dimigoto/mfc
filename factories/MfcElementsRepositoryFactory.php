<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\ReadSchemeFileHelper;
use common\modules\mfc\repositories\MfcElementsRepository;

class MfcElementsRepositoryFactory
{
    public function createRepository(?int $userId, bool $withArchive): MfcElementsRepository
    {
        $categoriesSchemeHelperFactory = new CategoriesSchemeHelperFactory(
            new ReadSchemeFileHelper()
        );

        $categoriesSchemeHelper = $categoriesSchemeHelperFactory->createObject(
            $userId,
            $withArchive
        );

        return new MfcElementsRepository(
            new MfcClassifierFactory(),
            $categoriesSchemeHelper
        );
    }
}
