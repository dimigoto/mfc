<?php

use common\dataProviders\CustomArrayDataProvider;
use common\models\LogError;
use common\modules\mfc\MfcModule;
use common\modules\mfc\models\MfcRequest;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var View $this */
/* @var CustomArrayDataProvider $dataProvider */

$this->title = MfcModule::t('backend', 'MFC_STALLED_SECTION_TITLE');
$this->params['breadcrumbs'][] = $this->title;

?>

<h1><?= Html::encode($this->title) ?></h1>
<div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => SerialColumn::class],
            'id',
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => static function (MfcRequest $model) {
                    return Html::a(
                        $model->user->userProfile->fullNameDisplay,
                        Url::toRoute(['/userUniver/default/view', 'id' => $model->user_id])
                    );
                },
                'label' => Yii::t('common', 'FULL_NAME'),
                'filter' => false,
            ],
            'typeDisplay',
            'attempts',
            'createdAtDisplay',
            [
                'attribute' => 'errorMessage',
                'format' => 'raw',
                'value' => static function (MfcRequest $model) {
                    $logError = LogError::find()
                        ->where(['entity_id' => $model->id])
                        ->orderBy(['id' => SORT_DESC])
                        ->one();

                    return $logError->description ?? '';
                },
                'label' => Yii::t('common', 'ERROR_MESSAGE'),
            ],
        ],
    ]) ?>
</div>
