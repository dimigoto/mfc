<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

use Yii;
use yii\bootstrap4\Html as BaseHtml;

class CustomHtml extends BaseHtml
{
    public static function encode($content, $doubleEncode = true)
    {
        return htmlspecialchars(
            (string)$content,
            ENT_COMPAT | ENT_SUBSTITUTE,
            Yii::$app->charset ?? 'UTF-8',
            $doubleEncode
        );
    }
}
