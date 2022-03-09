<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\readers;

use common\modules\mfc\interfaces\ReaderInterface;
use Yii;

/**
 * Чтение даты и времени
 */
class DateTimeValueReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function readValue($value, array $formElement, array $options = []): string
    {
        return Yii::$app->formatter->format($value, 'datetime');
    }
}
