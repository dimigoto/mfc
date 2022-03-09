<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\readers;

use common\modules\mfc\interfaces\ReaderInterface;
use common\modules\mfc\MfcModule;

/**
 * Чтение значения, выбранного из списка вариантов
 */
class ItemsValueReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function readValue($value, array $formElement, array $options = []): string
    {
        return MfcModule::t(
            'common',
            sprintf(
                '%s_ITEMS_%s',
                $formElement['label'],
                $value
            )
        );
    }
}
