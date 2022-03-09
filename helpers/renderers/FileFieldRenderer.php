<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\CustomHtml;

/**
 * Генератор поля выбора файла
 */
class FileFieldRenderer extends BaseFormFieldRenderer
{
    /**
     * {@inheritdoc}
     */
    public function renderInput(array $specification): string
    {
        $name = $this->getFieldName($specification['name']);

        if ($this->formSchemeHelper->isFileMultiple($specification)) {
            $name = sprintf('%s[]', $name);
            $options = $this->getOptions($specification);
            $options['multiple'] = 'multiple';
        } else {
            $fileExtensions = $this->formSchemeHelper->getFileExtensions($specification);
            $customRules = [
                'valid' => sprintf(
                    '/\.(%s)$/i',
                    implode('|', $fileExtensions)
                ),
            ];
            $options = $this->getOptions($specification, $customRules);
        }

        return CustomHtml::fileInput($name, null, $options);
    }
}
