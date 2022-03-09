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

$this->title = MfcModule::t('common', 'PAGE_TITLE');
$this->params['breadcrumbs'][] = $this->title;

MfcModuleAsset::register($this);

?>

<div class="container">
    <div class="row">
        <?php if (Yii::$app->user->can('undergraduate')) : ?>
            <?php if ($settleUserService->isFinalCourse()) : ?>
                <div class="col-12 col-md-6 col-lg-6 mb-2">
                    <a href="https://forms.office.com/r/w1Zetubvpp">
                        <div class="card">
                            <div class="card-body">
                                <h5>Трудоустройство и карьера выпускников</h5>
                                <p class="mb-0">Чем вы планируете заниматься после окончания университета?</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <hr>
    <div class="card-body search-global-card mb-2">
        <?= GlobalSearchWidget::widget(['config' => 'MFC', 'placeholder' => 'Поиск по услугам Единого окна..']) ?>
        <a class="maplink" href="/mfc/default/map">Карта услуг</a>
    </div>
    <h1 class="mb-4"><?= $this->title ?></h1>
    <div class="description mb-4">
        <p>Сервис Единое окно предоставляет доступ студентам и сотрудникам к различным услугам университета, помогает в
            оперативном получении консультации или решения возникшей проблемы.</p>
        <p>Для получения услуги необходимо перейти в один из представленных ниже разделов, заполнить форму и отправить
            заявку.</p>
    </div>
    <div class="row mfc-classifier">
        <?php

        foreach ($items as $item) {
            $linkTitle = $item->getTitle();
            $linkHint = $item->getHint();
            $icon = $item->getIcon();
            $subcategory = $item->getSubcategories(Yii::$app->user->identity->id);

            $linkBody = '';
            $subcategories = '';

            if (!empty($subcategory)) {
                foreach ($subcategory as $category) {
                    $subcategories .= '<object>' . Html::a(
                        Html::tag('p', $category->getTitle(), ['class' => 'mfc-classifier_subcategories']),
                        $category->getUrl()
                    ) .
                    '</object>';
                }
            }

            $linkBody .= Html::tag('span', '', ['class' => sprintf('mfc-icon mfc-service-icon__%s', $icon)]);

            if (!empty($linkHint) && strlen($linkHint) > 1) {
                $linkTitle .= Html::tag('span', $linkHint, ['class' => 'mfc-classifier__hint']);
            }

            $linkTitleContainer = Html::tag(
                'span',
                sprintf('%s%s%s', $linkBody, $linkTitle, $subcategories),
                ['class' => 'mfc-classifier__title card p-3 mb-3 border shadow-sm']
            );


            $link = Html::a($linkTitleContainer, $item->getUrl());

            print Html::tag(
                'div',
                $link,
                ['class' => 'col-sm-4']
            );
        }

        ?>
    </div>
</div>