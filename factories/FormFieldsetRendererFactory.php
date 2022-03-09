<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\FormContentRenderHelper;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\helpers\renderers\EducationInfoFieldsRenderer;
use common\modules\mfc\helpers\renderers\EmployeeDepartmentInfoFieldsRenderer;
use common\modules\mfc\helpers\renderers\EmptyFieldsRenderer;
use common\modules\mfc\helpers\renderers\YearsIntervalFieldsRenderer;
use common\modules\mfc\interfaces\FormFieldsetRendererInterface;

/**
 * Фабрика генераторов групп полей ввода
 */
class FormFieldsetRendererFactory extends BaseFormFieldsetRendererFactory
{
    public const EDUCATION_INFO_TYPE = 'educationInfo';
    public const EMPLOYEE_DEPARTMENT_TYPE = 'employeeDepartmentInfo';
    public const YEARS_INTERVAL_TYPE = 'yearsInterval';

    /**
     * {@inheritdoc}
     */
    public function createRenderer(string $type, FormSchemeHelper $formSchemeHelper): FormFieldsetRendererInterface
    {
        $formContentRenderHelper = new FormContentRenderHelper(
            $this->user,
            $formSchemeHelper
        );

        switch ($type) {
            case self::EDUCATION_INFO_TYPE:
                return new EducationInfoFieldsRenderer($this->user, $formContentRenderHelper);
            case self::EMPLOYEE_DEPARTMENT_TYPE:
                return new EmployeeDepartmentInfoFieldsRenderer($this->user, $formContentRenderHelper);
            case self::YEARS_INTERVAL_TYPE:
                return new YearsIntervalFieldsRenderer($this->user, $formContentRenderHelper);
            default:
                return new EmptyFieldsRenderer();
        }
    }
}
