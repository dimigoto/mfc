<?php

use common\modules\mfc\assets\MfcModuleAsset;
use common\modules\mfc\interfaces\MenuItemInterface;
use common\modules\mfc\MfcModule;
use yii\helpers\Html;
use yii\web\View;
use common\modules\settle\services\SettleUserService;
use common\modules\search\widgets\GlobalSearchWidget\GlobalSearchWidget;

/* @var View $this */
/* @var int $userId ID пользователя */
/* @var MenuItemInterface[] $items Элементы навигации */
/* @var SettleUserService $settleUserService */

$this->title = MfcModule::t('common', 'MAP_TITLE');

MfcModuleAsset::register($this);
function printChildren ($children){
    ob_start();?>
    <ul>
        <?php
        $titles = [];
        foreach ($children as $child):
            if (isset($titles[$child['item']->getTitle()])){
                continue;
            }
            $titles[$child['item']->getTitle()] = true;
            ?>
            <li>
                <p><a href="<?=$child['item']->getUrl()?>"><?=$child['item']->getTitle() ?></a></p>
                <?php if (!empty($child['children'])){
                    printChildren($child['children']);
                } ?>
            </li>
        <?php endforeach;?>
    </ul>
    <?php ob_end_flush();
}
?>
<div class="container">
    <h1 class="mb-4"><?= $this->title ?></h1>
    <div class="description mb-4">
    </div>
    <div class="row mfc-classifier">
        <?php printChildren($tree)?>
    </div>
</div>