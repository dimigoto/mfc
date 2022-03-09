<?php

namespace common\modules\mfc\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

class MfcModuleAsset extends AssetBundle
{
    public $sourcePath = '@common/modules/mfc/dist';

    public $css = [
        'css/expromptum_style.css',
        'css/mfc_style.css',
    ];

    public $js = [
        'js/mfc.js',
    ];

    public $depends = [
        YiiAsset::class,
    ];
}
