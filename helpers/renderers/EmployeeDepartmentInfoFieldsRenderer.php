<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\Helpers\JsonHelper;
use common\modules\mfc\helpers\FormContentRenderHelper;
use common\modules\userUniver\models\User;
use common\modules\userUniver\repositories\EmployeeProfileRepository;
use yii\helpers\ArrayHelper;

/**
 * Генератор поля выбора подразделения и зависимых полей
 */
class EmployeeDepartmentInfoFieldsRenderer extends BaseFormFieldsetRenderer
{
    private FormContentRenderHelper $formContentRenderHelper;
    private array $items = [];

    public function __construct(User $user, FormContentRenderHelper $formContentRenderHelper)
    {
        $this->formContentRenderHelper = $formContentRenderHelper;

        $employeeProfileRepository = new EmployeeProfileRepository();
        $employeeProfiles = $employeeProfileRepository->findAllByUserId($user->id);

        foreach ($employeeProfiles as $employeeProfile) {
            $department = $employeeProfile->department;

            if (!$department) {
                continue;
            }

            $post = $employeeProfile->employeePost;

            if (!$post) {
                continue;
            }

            $this->items[] = [
                'name' => $department->name,
                'guid' => $department->guid,
                'post' => $post->name,
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $_specification['children'][0]['itemsAsIs'] = $this->getItemsColumn('name');

        return $this->formContentRenderHelper->renderElements($_specification['children']);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomJavascript(): string
    {
        return '
           var mfcEmployeeDepartments = ' . JsonHelper::jsonEncode($this->getItemsColumn('guid')) . ';
           var mfcEmployeePosts = ' . JsonHelper::jsonEncode($this->getItemsColumn('post')) . ';
           
           function updateEmployeeDepartmentDependentField(ed, id, values) {
               if (ed in values) {
                   $(id).val(values[ed]);
                   $(id).removeAttr("required"); 
                   $(id).attr("readonly", true);
                   $(id).addClass("readonly"); 
               } else {
                   $(id).val("");
                   $(id).attr("required", true);
                   $(id).removeAttr("readonly");
                   $(id).removeClass("readonly");
               } 
           }
           
           function updateEmployeeDepartmentDependentFields() {
               var ed = $("#employeeDepartment").val();
               
               updateEmployeeDepartmentDependentField(
                   ed,
                   "#employeeDepartmentGuid",
                   mfcEmployeeDepartments
               );
               
               updateEmployeeDepartmentDependentField(
                   ed,
                   "#employeePost",
                   mfcEmployeePosts
               );
           }
           
           updateEmployeeDepartmentDependentFields();
            
           $("#employeeDepartment").change(function () {
               updateEmployeeDepartmentDependentFields();
           });
        ';
    }

    /**
     * @param string $columnName
     *
     * @return array
     */
    private function getItemsColumn(string $columnName): array
    {
        return ArrayHelper::getColumn(
            ArrayHelper::index($this->items, 'name'),
            $columnName
        );
    }
}
