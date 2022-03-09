<?php

declare(strict_types=1);

namespace common\modules\mfc\services;

use common\modules\userRequest\models\records\UserRequestRecord;
use common\modules\userRequest\models\UserRequest;

/**
 * Сервис по обновления заявок "Единого окна"
 */
class MfcRequestUpdateFrom1CService
{
    private const STATUS_1C_IN_PROGRESS = 'IN_PROGRESS';
    private const STATUS_1C_DONE = 'DONE';

    private const STATUSES_MAP = [
        self::STATUS_1C_IN_PROGRESS => UserRequest::STATUS_IN_PROGRESS,
        self::STATUS_1C_DONE => UserRequest::STATUS_DONE,
    ];

    /**
     * @param array $data
     */
    public function updateStatuses(array $data): void
    {
        foreach ($data as $dataItem) {
            if (empty($dataItem['number']) || empty($dataItem['status'])) {
                continue;
            }

            $status = $this->translateStatus($dataItem['status']);

            if (null === $status) {
                continue;
            }

            $userRequestRecord = UserRequestRecord::find()
                ->where(['number_foreign' => $dataItem['number']])
                ->one();

            if ($userRequestRecord) {
                $userRequestRecord->status = $status;
                $userRequestRecord->save();
            }
        }
    }

    /**
     * Перевод статуса из 1С в локальный статус заявки
     *
     * @param string $status1C Статус заявки из 1С
     *
     * @return int|null
     */
    private function translateStatus(string $status1C): ?int
    {
        return self::STATUSES_MAP[$status1C] ?? null;
    }
}
