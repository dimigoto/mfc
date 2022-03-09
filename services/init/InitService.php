<?php
/**
 * Created by PhpStorm.
 * User: Karavanov Dmitriy
 * Date: 06/06/2019
 * Time: 11:57
 */

namespace common\modules\mfc\services\init;

use common\services\BaseInitService;

class InitService extends BaseInitService
{

    /**
     * Запускает инициализацию модуля
     *
     * @return bool
     */
    public function run(): bool
    {
        // Запускаем миграции
        if (!$this->migrate()) {
            return false;
        }

        return true;
    }


    /**
     * Запускает деинсталяцию модуля
     *
     * @return bool
     */
    public function destroy(): bool
    {
        if (!$this->migrateDown()) {
            return false;
        }

        return true;
    }
}
