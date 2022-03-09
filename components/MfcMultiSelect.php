<?php

declare(strict_types=1);

namespace common\modules\mfc\components;

use common\modules\mfc\helpers\CustomHtml;
use dosamigos\multiselect\MultiSelect;

/**
 * MultiSelect
 */
class MfcMultiSelect extends MultiSelect
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo CustomHtml::activeDropDownList($this->model, $this->attribute, $this->data, $this->options);
        } else {
            echo CustomHtml::dropDownList($this->name, $this->value, $this->data, $this->options);
        }

        $this->registerPlugin();
    }
}
