<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\models\MfcRequest;
use common\modules\userRequest\models\UserRequest;

class MfcRequestFactory
{
    public function createMfcRequest(
        string $subtype,
        string $data,
        string $phone,
        string $comment,
        int $userId
    ): MfcRequest {
        $result = new MfcRequest();
        $result->type = MfcRequest::TYPE;
        $result->phone = $phone;
        $result->comment = $comment;
        $result->user_id = $userId;
        $result->status = UserRequest::STATUS_CREATED;
        $result->subtype = $subtype;
        $result->data = $data;

        return $result;
    }
}
