<?php

use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Breadcrumbs;
use common\modules\mfc\MfcModule;
use common\modules\mfc\assets\MfcModuleAsset;
use common\modules\mfc\helpers\FormContentRenderHelper;
use common\modules\mfc\models\MfcCategory;
use common\modules\mfc\models\MfcEnquiryType;

/* @var View $this */
/* @var MfcEnquiryType $enquiryType */
/* @var MfcCategory[] $parents */
/* @var FormContentRenderHelper $formRenderHelper */

$this->title = $enquiryType->getTitle();

$this->params['breadcrumbs'][] = [
    'label' => MfcModule::t('common', 'PAGE_TITLE'),
    'url' => ['index'],
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
                'class' => 'breadcrumb breadcrumb__un',
            ],
        ]
    ) ?>
    <h1 class="mb-4"><?= $this->title ?></h1>
    <div class="row mt-4 d-print-block">
        <div class="col-12">
            <div class="card shadow-sm d-print-block">
                <div class="card-body">
                    <section id="mfc-enquiry-description" class="mfc-enquiry-description mb-4">
                        <!--<p>
                            <a class="btn btn-primary"
                               data-toggle="collapse" href="#mfcEnquiryTypeInfo" role="button" aria-expanded="false"
                               aria-controls="mfcEnquiryTypeInfo">
                                Информация об услуге
                            </a>
                        </p>
                        <div class="collapse" id="mfcEnquiryTypeInfo">
                            <div class="card card-body">

                            </div>
                        </div>-->
                        <?= $enquiryType->getDescription() ?>
                    </section>
                    <section id="mfc-enquiry-downloads" class="mfc-enquiry-downloads">
                    <?php if ($enquiryType->hasFileToDownload()) {
                        print Html::tag(
                            'p',
                            'Для получения услуги скачайте и заполните бланк заявления',
                            [
                                'class' => 'alert alert-info',
                            ]
                        );

                        $formsFiles = $enquiryType->getFormsFiles();

                        if (count($formsFiles) === 1) {
                            print Html::a(
                                MfcModule::t(
                                    'common',
                                    'DOWNLOAD_APPLICATION_FORM_LINK'
                                ),
                                Url::toRoute([
                                    'download-file',
                                    'id' => $enquiryType->getId(),
                                    'fileId' => $formsFiles[0]['guid'],
                                ]),
                                [
                                    'role' => 'button',
                                    'class' => 'btn btn-primary',
                                    'style' => 'margin-bottom: 40px;',
                                ]
                            );
                        } else {
                            $dropdown = '';
                            $dropdown .= Html::a(
                                MfcModule::t(
                                    'common',
                                    'DOWNLOAD_APPLICATION_FORM_LINK'
                                ),
                                '#',
                                [
                                    'role' => 'button',
                                    'class' => 'btn btn-primary dropdown-toggle',
                                    'id' => 'dropdownFormsFilesMenuLink',
                                    'data-toggle' => 'dropdown',
                                    'aria-haspopup' => 'true',
                                    'aria-expanded' => 'false',
                                ]
                            );

                            $dropdownMenu = '';

                            foreach ($formsFiles as $formFile) {
                                $dropdownMenu .= Html::a(
                                    MfcModule::t('common', $formFile['label']),
                                    Url::toRoute([
                                        'download-file',
                                        'id' => $enquiryType->getId(),
                                        'fileId' => $formFile['guid'],
                                    ]),
                                    [
                                        'class' => 'dropdown-item',
                                    ]
                                );
                            }

                            $dropdown .= Html::tag(
                                'div',
                                $dropdownMenu,
                                [
                                    'class' => 'dropdown-menu',
                                    'aria-labelledby' => 'dropdownFormsFilesMenuLink',
                                ]
                            );

                            print Html::tag(
                                'div',
                                $dropdown,
                                ['style' => 'margin-bottom: 40px;']
                            );
                        }
                    } ?>
                    </section>
                    <?= Html::beginForm(
                        Url::toRoute(['default/save-request']),
                        'post',
                        [
                            'id' => 'mfcEnquiryForm',
                            'class' => 'mfcEnquiryForm',
                        ]
                    ) ?>
                    <div id="mfcEnquiryFormContainer" class="mfcEnquiryForm__container">
                        <?= $formRenderHelper->render($enquiryType->getStructure(), $enquiryType->getId()) ?>
                        <button type="button" class="btn btn-primary" id="mfcEnquiryFormSubmit" data-xp="type: 'submit', enabled_on_completed: true">
                            <div class="d-flex align-items-center">
                                <span class="js-spinner spinner-border text-light spinner-border-sm mr-1" style="display: none;" role="status">
                                    <span class="sr-only">Loading...</span>
                                </span>
                                <span>Отправить заявку</span>
                            </div>
                        </button>
                    </div>
                    <div id="mfcEnquiryFormResult" class="mfcEnquiryForm__result"></div>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

$this->registerJs($formRenderHelper->getCustomJavascript());