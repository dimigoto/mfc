<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\readers;

use common\modules\mfc\interfaces\ReaderInterface;

/**
 * Отмена чтения параметра
 */
class NoneValueReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function readValue($value, array $formElement, array $options = []): string
    {
        return '';
    }
}
