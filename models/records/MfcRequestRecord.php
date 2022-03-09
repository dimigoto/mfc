<?php

declare(strict_types=1);

namespace common\modules\mfc\models\records;

use common\modules\userRequest\models\records\UserRequestRecord;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $request_id Внешний ключ для таблицы user_request
 * @property string $subtype Тип заявки в "Единое окно"
 * @property string $data Содержание заявки
 *
 * @property-read UserRequestRecord $userRequest
 */
class MfcRequestRecord extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mfc_request}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['request_id'],
                'integer',
            ],
            [
                ['request_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => UserRequestRecord::class,
                'targetAttribute' => ['request_id' => 'id'],
            ],
            [
                ['subtype', 'data'],
                'string',
            ],
        ];
    }

    /**
     * Базовая часть заявки
     *
     * @return ActiveQuery
     */
    public function getUserRequest(): ActiveQuery
    {
        return $this->hasOne(UserRequestRecord::class, ['id' => 'request_id']);
    }
}
