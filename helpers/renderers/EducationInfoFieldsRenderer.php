<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\Helpers\JsonHelper;
use common\modules\mfc\helpers\FormContentRenderHelper;
use common\modules\userUniver\models\User;
use common\modules\userUniver\repositories\StudentProfileRepository;

/**
 * Генератор поля выбора образовательной программы и зависимых полей
 */
class EducationInfoFieldsRenderer extends BaseFormFieldsetRenderer
{
    private FormContentRenderHelper $formContentRenderHelper;
    private array $educationPrograms = [];
    private array $courses = [];
    private array $academicGroups = [];
    private array $educationForms = [];
    private array $compensationTypes = [];
    private array $studentDepartmentsNames = [];
    private array $studentDepartmentsGuids = [];

    public function __construct(User $user, FormContentRenderHelper $formContentRenderHelper)
    {
        $this->formContentRenderHelper = $formContentRenderHelper;

        $studentProfileRepository = new StudentProfileRepository();
        $studentProfiles = $studentProfileRepository->findAllByUserId($user->id);

        foreach ($studentProfiles as $studentProfile) {
            $educationProgram = $studentProfile->educationProgram;

            if (!$educationProgram) {
                continue;
            }

            $this->educationPrograms[$educationProgram->name] = $educationProgram->name;

            $course = $studentProfile->course;

            $this->courses[$educationProgram->name] = $course->name ?? '';

            $academicGroup = $studentProfile->academicGroup;

            $this->academicGroups[$educationProgram->name] = $academicGroup->name ?? '';

            $educationForm = $educationProgram->educationForm;
            $this->educationForms[$educationProgram->name] = $educationForm->name;

            $compensationType = $studentProfile->compensationType;
            $this->compensationTypes[$educationProgram->name] = $compensationType->name;

            $studentDepartment = $studentProfile->educationProgram->department;
            $this->studentDepartmentsNames[$educationProgram->name] = $studentDepartment->name;
            $this->studentDepartmentsGuids[$educationProgram->name] = $studentDepartment->guid;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $_specification['children'][0]['itemsAsIs'] = $this->educationPrograms;

        return $this->needSkip($_specification)
            ? ''
            : $this->formContentRenderHelper->renderElements($_specification['children']);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomJavascript(): string
    {
        return '
           var mfcCourses = ' . JsonHelper::jsonEncode($this->courses) . ';
           var mfcAcademicGroups = ' . JsonHelper::jsonEncode($this->academicGroups) . ';
           var mfcEducationForms = ' . JsonHelper::jsonEncode($this->educationForms) . ';
           var mfcCompensationTypes = ' . JsonHelper::jsonEncode($this->compensationTypes) . ';
           var mfcStudentDepartmentsNames = ' . JsonHelper::jsonEncode($this->studentDepartmentsNames) . ';
           var mfcStudentDepartmentsGuids = ' . JsonHelper::jsonEncode($this->studentDepartmentsGuids) . ';
           
           function updateEducationProgramDependentField(ep, id, values) {
               if (ep in values) {
                   $(id).val(values[ep]);
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
           
           function updateEducationProgramDependentFields() {
               var ep = $("#educationProgram").val();
               
               updateEducationProgramDependentField(ep, "#course", mfcCourses);
               updateEducationProgramDependentField(ep, "#academicGroup", mfcAcademicGroups);
               updateEducationProgramDependentField(ep, "#educationForm", mfcEducationForms);
               updateEducationProgramDependentField(ep, "#compensationType", mfcCompensationTypes);
               updateEducationProgramDependentField(ep, "#studentDepartment", mfcStudentDepartmentsNames);
               updateEducationProgramDependentField(ep, "#studentDepartmentGuid", mfcStudentDepartmentsGuids);
           }
           
           updateEducationProgramDependentFields();
            
           $("#educationProgram").change(function () {
               updateEducationProgramDependentFields();
           });
        ';
    }

    /**
     * @param array $specification
     *
     * @return bool
     */
    protected function needSkip(array $specification): bool
    {
        return empty($specification['children'][0]['itemsAsIs']);
    }
}
