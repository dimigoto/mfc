<?php

use common\modules\mfc\assets\MfcModuleAsset;
use common\modules\mfc\interfaces\MenuItemInterface;
use common\modules\mfc\MfcModule;
use common\modules\mfc\models\MfcCategory;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Breadcrumbs;
use common\modules\search\widgets\GlobalSearchWidget\GlobalSearchWidget;

/* @var View $this */
/* @var string $categoryName Название категории */
/* @var MenuItemInterface[] $items Дочерние элементы */
/* @var MfcCategory[] $parents */
/* @var string $categoryId ID категории */

$this->title = $categoryName;

$this->params['breadcrumbs'][] = [
    'label' => MfcModule::t('common', 'PAGE_TITLE'),
    'url' => ['index']
];

if (!empty($parents)) {
    foreach ($parents as $parent) {
        $this->params['breadcrumbs'][] = [
            'label' => $parent->getTitle(),
            'url' => ['category', 'id' => $parent->getId()],
        ];
    }
}

MfcModuleAsset::register($this);

?>

<div class="container">
    <?=
    Breadcrumbs::widget(
        [
            'itemTemplate' => "<li class=\"breadcrumb-item breadcrumb-item__un\">{link}</li>\n",
            'activeItemTemplate' => "<li class=\"breadcrumb-item active\"> {link} </li>\n",
            'links' => $this->params['breadcrumbs'] ?? [],
            'options' => [
                'class' => 'breadcrumb breadcrumb__un'
            ],
        ]
    ) ?>
    <div class="card-body search-global-card mb-2">
        <?= GlobalSearchWidget::widget(['config' => 'MFC', 'placeholder' => 'Поиск по услугам Единого окна..']) ?>
        <a class="maplink" href="/mfc/default/map">Карта услуг</a>
    </div>
    <h1 class="mb-4"><?= $this->title ?></h1>
    <?php if ($categoryId === MfcModule::t('common', 'GET_SERVICE')) : ?>
        <div class="description mb-4">
            <p>Данный раздел предоставляет возможность заказать услуги, предоставляемые студенческим офисом, службами
                обеспечения работы кампуса, кадровой службой и бухгалтерией ДВФУ.</p>
        </div>
    <?php endif; ?>
    <?php if ($categoryId === MfcModule::t('common', 'GET_CONSULTATIONS')) : ?>
        <div class="description mb-4">
            <p>Данный раздел предназначен для решения вопросов, возникающих в связи с обучением, проживанием на кампусе,
                а также дополнительными возможностями, предоставляемыми университетом.</p>
            <p>Для получения услуги выберите категорию в тематическом каталоге или оставьте открытое обращение и
                дождитесь ответа профильной службы.</p>
            <p>Срок рассмотрения до 10 рабочих дней, в зависимости от сложности запроса. При отправке открытого
                обращения срок реакции может быть увеличен, так как необходимо дополнительное время на поиск профильного
                подразделения для решения возникшего вопроса.</p>
        </div>
    <?php endif; ?>
    <?php if ($categoryId === MfcModule::t('common', 'MAKE_ENQUIRIES')) : ?>
        <div class="description mb-4">
            <p>Данный раздел предназначен для оформления официального обращения в различные службы университета.
                Официальное обращение оформляется при необходимости решения глобальных вопросов, влияющих на
                деятельность всего университета или его отдельных служб.</p>
            <p>Обратите внимание, что реакция на официальные обращения требует больше времени, так как они
                рассматриваются руководителями структурных подразделений. Для работы с такими обращениями может
                потребоваться запрос дополнительной информации и материалов, в том числе от смежных служб.</p>
            <p>При оформлении официального сообщения инициатору нужно быть готовым активно участвовать в процессе
                рассмотрения обращения, отвечать на телефонные звонки и запросы, поступающие на корпоративную почт.</p>
            <p>Срок рассмотрения: до 40 рабочих дней, в зависимости от сложности запроса.</p>
        </div>
    <?php endif; ?>
    <div class="mfc-classifier row mt-4 d-print-block">
        <?php

        $titles = [];
        foreach ($items as $item) {
            if (isset($titles[$item->getTitle()])){
                continue;
            }
            $titles[$item->getTitle()] = true;
            $linkTitle = $item->getTitle();
            $linkHint = $item->getHint();
            $icon = $item->getIcon();
            $subcategories = $item->getSubcategories(Yii::$app->user->identity->id);

            $linkBody = '';
            $subcategoriesLinks = '';

            $subTitles = [];
            foreach ($subcategories as $category) {
                if (isset($subTitles[$category->getTitle()])){
                    continue;
                }
                $subTitles[$category->getTitle()] = true;
                $subcategoriesLinks .= Html::tag(
                    'object',
                    Html::a(
                        Html::tag('p', $category->getTitle(), ['class' => 'mfc-classifier_subcategories']),
                        $category->getUrl()
                    )
                );
            }

            $linkBody .= Html::tag(
                'span',
                '',
                ['class' => sprintf('mfc-icon mfc-service-icon__%s', $icon)]
            );

            if (!empty($linkHint) && strlen($linkHint) > 1) {
                $linkTitle .= Html::tag('span', $linkHint, ['class' => 'mfc-classifier__hint']);
            }

            $linkTitleContainer = Html::tag(
                'span',
                sprintf('%s%s%s', $linkBody, $linkTitle, $subcategoriesLinks),
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
