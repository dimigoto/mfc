<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\components\MfcMultiSelect;
use Yii;

/**
 * Генератор выпадающего списка с множественным выбором
 */
class MultiselectFieldRenderer extends BaseFormFieldRenderer
{
    /**
     * {@inheritdoc}
     */
    public function renderInput(array $specification): string
    {
        if ($this->formSchemeHelper->isRequired($specification)) {
            $customRules = [
                'valid' => '\'[this] && [this].val().length > 0\'',
            ];
        } else {
            $customRules = [];
        }

        $options = $this->getOptions($specification, $customRules);
        $options['multiple'] = 'multiple';

        return MfcMultiSelect::widget([
            "options" => $options,
            'data' => $this->getItems($specification),
            'name' => $this->getFieldName($specification['name']),
            "clientOptions" => [
                'nonSelectedText' => Yii::t('forms', 'MULTISELECT_NONE_SELECTED'),
                'nSelectedText' => Yii::t('forms', 'MULTISELECT_N_SELECTED'),
                'allSelectedText' => Yii::t('forms', 'MULTISELECT_ALL_SELECTED'),
                'disabledText' => Yii::t('forms', 'MULTISELECT_DISABLED'),
                'resetText' => Yii::t('forms', 'MULTISELECT_RESET'),
                'maxHeight' => 400,
            ],
        ]);
    }
}
