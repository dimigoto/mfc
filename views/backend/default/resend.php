<?php

use common\dataProviders\CustomArrayDataProvider;
use common\modules\mfc\MfcModule;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/* @var View $this */
/* @var CustomArrayDataProvider $dataProvider */

$this->title = MfcModule::t('backend', 'MFC_RESEND_SECTION_TITLE');
$this->params['breadcrumbs'][] = $this->title;

$formId = 'mfc-resend-form';
$resultContainerId = $formId . '-result';
$resultMessageId = $formId . '-message';

?>

<h1><?= Html::encode($this->title) ?></h1>

<div id="<?=$resultContainerId?>" class="js-pjax-result pjax-result"></div>
<div class="box box-primary">
    <div class="box-body">
        <?php
        Pjax::begin(
            [
                'enablePushState' => false,
                'clientOptions' => [
                    'container' => '#' . $resultContainerId
                ],
                'options' => [
                    'id' => 'table-user__' . $formId,
                ]
            ]
        );
        print Html::beginForm(
            Url::toRoute(['/mfc/default/resend-process']),
            'post',
            [
                'data' => [
                    'pjax' => true,
                    'result-container' => $resultContainerId,
                    'message-container' => $resultMessageId,
                ],
                'id' => $formId,
                'class' => 'js-pjax-form',
            ]
        );

        print Html::tag(
            'button',
            MfcModule::t(
                'backend',
                'MFC_RESEND_BUTTON'
            ),
            [
                'class' => 'btn btn-default',
                'type' => 'submit',
            ]
        );

        print Html::endForm();
        Pjax::end();
        ?>
    </div>
    <div id="<?=$resultMessageId?>"></div>
</div>