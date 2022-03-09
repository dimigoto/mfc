<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\readers\DateTimeValueReader;
use common\modules\mfc\helpers\readers\DateValueReader;
use common\modules\mfc\helpers\readers\FileValueReader;
use common\modules\mfc\helpers\readers\ItemsValueReader;
use common\modules\mfc\helpers\readers\NoneValueReader;
use common\modules\mfc\helpers\readers\StringValueReader;
use common\modules\mfc\interfaces\ReaderInterface;

/**
 * Фабрика считывателей сохранённых значений
 */
class ReaderFactory
{
    public const DATE_READER = 'date';
    public const DATE_TIME_READER = 'datetime';
    public const FILE_READER = 'file';
    public const ITEMS_READER = 'items';
    public const NONE_READER = 'none';

    /**
     * Создание считывателя
     *
     * @param string $readFormat Формат чтения
     *
     * @return ReaderInterface
     */
    public function createReader(string $readFormat): ReaderInterface
    {
        switch ($readFormat) {
            case self::DATE_READER:
                return new DateValueReader();
            case self::DATE_TIME_READER:
                return new DateTimeValueReader();
            case self::FILE_READER:
                return new FileValueReader();
            case self::ITEMS_READER:
                return new ItemsValueReader();
            case self::NONE_READER:
                return new NoneValueReader();
            default:
                return new StringValueReader();
        }
    }
}
