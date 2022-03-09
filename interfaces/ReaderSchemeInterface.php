<?php

namespace common\modules\mfc\interfaces;

/**
 *
 */
interface ReaderSchemeInterface
{
    /**
     * Чтение схемы
     *
     * @param string $name
     *
     * @return array
     */
    public function readScheme(string $name): array;
}
