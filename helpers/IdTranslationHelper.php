<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

/**
 * Преобразование идентификаторов элементов Единого окна в имена констант заголовков и описаний заявок
 */
class IdTranslationHelper
{
    /**
     * Имя строковой константы
     *
     * @param string $prefix Префикс
     * @param string $id ID
     * @param string $suffix Суффикс
     *
     * @return string
     */
    public function getTranslationId(string $prefix, string $id, string $suffix): string
    {
        return sprintf(
            '%s_%s_%s',
            $prefix,
            str_replace('-', '_', strtoupper($id)),
            $suffix
        );
    }
}
