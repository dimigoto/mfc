<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\Helpers\JsonHelper;
use common\modules\mfc\helpers\FormContentRenderHelper;
use common\modules\userUniver\models\User;
use common\modules\userUniver\repositories\StudentProfileRepository;
use Exception;

/**
 * Генератор группы полей ввода выбора интервала лет
 */
class YearsIntervalFieldsRenderer extends BaseFormFieldsetRenderer
{
    private FormContentRenderHelper $formContentRenderHelper;
    private array $yearsStart = [];

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

            $this->yearsStart[$educationProgram->name] = $studentProfile->entrance_year;
        }
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function render(array $specification): string
    {
        $_specification = $specification;

        return $this->formContentRenderHelper->renderElements($_specification['children']);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomJavascript(): string
    {
        return '
            var mfcYearsStart = ' . JsonHelper::jsonEncode($this->yearsStart) . ';
            
            function updateEducationProgramDependentField(ep, id) {
                var dateCurrent = new Date();
                var yearTo = dateCurrent.getFullYear();
                var yearFrom = parseInt(mfcYearsStart[ep]);
                var yearSelected = parseInt($(id).val());
                
                var options = [];
                var option;
                
                for (yearCounter = yearTo; yearCounter >= yearFrom; yearCounter--) {
                    option = $("<option />")
                    option.val(yearCounter);
                    option.text(yearCounter);
                    
                    if (yearCounter === yearSelected) {
                        option.attr("selected", "selected");
                    }
                    
                    options.push(option);
                }
                
                $("option", id).remove(); 
                $(id).append(options);
                //$(id)
            }
           
            function updateEducationProgramDependentFields() {
                var ep = $("#educationProgram").val();
               
                updateEducationProgramDependentField(ep, "#periodFrom");
                updateEducationProgramDependentField(ep, "#periodTo");
            }
           
            if ($("#educationProgram").length) {
                updateEducationProgramDependentFields();
                
                $("#educationProgram").change(function () {
                    updateEducationProgramDependentFields();
                });                
            }
        ';
    }
}
