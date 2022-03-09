<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\readers;

use common\modules\mfc\interfaces\ReaderInterface;

/**
 * Чтение текстового параметра
 */
class StringValueReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function readValue($value, array $formElement, array $options = []): string
    {
        return (is_array($value)) ? '' : $value;
    }
}
