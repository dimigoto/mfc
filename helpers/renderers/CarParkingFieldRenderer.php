<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\Helpers\ArrayHelper;
use common\Helpers\JsonHelper;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\mfc\MfcModule;
use common\modules\settle\factories\AccommodationServiceFactory;
use common\modules\settle\interfaces\AccommodationServiceInterface;
use common\modules\userUniver\models\User;
use common\modules\userUniver\services\UserRoleService;
use common\repositories\DictionaryBuildingRepository;

/**
 * Генератор выпадающего списка с множественным выбором для выбора парковки
 */
class CarParkingFieldRenderer implements FormElementRendererInterface
{
    private const PASS_CATEGORY_BASIC = 'BASIC';
    private const PASS_CATEGORY_SUPER = 'SUPER';
    private const PASS_CATEGORY_PERIOD = 'PERIOD';

    private FormSchemeHelper $formSchemeHelper;
    private User $user;
    private UserRoleService $userRoleService;
    private AccommodationServiceInterface $accommodationService;
    private DictionaryBuildingRepository $dictionaryBuildingRepository;

    public function __construct(FormSchemeHelper $formSchemeHelper, User $user)
    {
        $this->formSchemeHelper = $formSchemeHelper;
        $this->user = $user;
        $this->userRoleService = new UserRoleService();

        $accommodationServiceFactory = new AccommodationServiceFactory();
        $this->accommodationService = $accommodationServiceFactory->createService($user);

        $this->dictionaryBuildingRepository = new DictionaryBuildingRepository();
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        $_specification = $specification;
        $buildings = $this->getBuildings();
        $_specification['itemsAsIs'] = $buildings[self::PASS_CATEGORY_BASIC];

        $fieldRenderer = new MultiselectFieldRenderer($this->formSchemeHelper);

        return $fieldRenderer->render($_specification);
    }

    /**
     * Здания
     *
     * @return array
     */
    private function getBuildings(): array
    {
        $gr1 = MfcModule::t('common', 'CAR_PASS_PARKING_ITEMS_GR1');
        $gr2 = MfcModule::t('common', 'CAR_PASS_PARKING_ITEMS_GR2');
        $gr3 = MfcModule::t('common', 'CAR_PASS_PARKING_ITEMS_GR3');

        $grList = [
            $gr1 => $gr1,
            $gr2 => $gr2,
        ];

        if (
            $this->userRoleService->isEmployeeByLocalRoles($this->user->id)
            || $this->userRoleService->isGraduateByLocalRoles($this->user->id)
        ) {
            $grList[$gr3] = $gr3;
        }

        $educationalBuildings = $this->dictionaryBuildingRepository->getAllActiveNamedByLetterAsArray();

        $educationalBuildingsList = ArrayHelper::map($educationalBuildings, 'name', 'name');

        $dormitories = $this->accommodationService->getAccommodationData();

        $dormitoriesList = array_merge(
            ArrayHelper::map($dormitories[1], 'name', 'name'),
            ArrayHelper::map($dormitories[2], 'name', 'name')
        );

        $result = [];
        $result[self::PASS_CATEGORY_BASIC] = $grList;
        $result[self::PASS_CATEGORY_SUPER] = array_merge(
            $grList,
            $educationalBuildingsList,
            $dormitoriesList
        );
        $result[self::PASS_CATEGORY_PERIOD] = $result[self::PASS_CATEGORY_SUPER];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomJavascript(): string
    {
        return '
            var parking = ' . JsonHelper::jsonEncode($this->getBuildings()) . ';
            var parkingSelect = $("#parking");
            
            parkingSelect.multiselect(
                "setOptions",
                 {
                    onChange: function(option, checked) {
                        updateSelectedParking();
                    }
                }
            );
            
            parkingSelect.multiselect("rebuild");
            
            function updateSelectedParking() {
                var selectedOptions = getSelectedParking();
         
                if (selectedOptions.length >= 3) {
                    var nonSelectedOptions = parkingSelect.find("option").filter(function() {
                        return !$(this).is(":selected");
                    });
 
                    nonSelectedOptions.each(function() {
                        var input = $(\'input[value="\' + $(this).val() + \'"]\');
                        input.prop("disabled", true);
                        input.parent(".multiselect-option").addClass("disabled");
                    });
                } else {
                    parkingSelect.find("option").each(function() {
                        var input = $(\'input[value="\' + $(this).val() + \'"]\');
                        input.prop("disabled", false);
                        input.parent(".multiselect-option").addClass("disabled");
                    });
                }
            }
            
            function getSelectedParking() {
                return parkingSelect.find("option:selected");
            }
           
            function updateParking() {
                var passCategory = $("#passCategory").val();
                var selectedParking = getSelectedParking();
                var selectedParkingList = [];
                
                $.each(selectedParking, function (key, val) {
                    selectedParkingList.push($(val).attr("value"));
                });
                
                var data = [];
                
                if (passCategory) {
                    $.each(
                        parking[passCategory],
                        function (key, val) {
                            data.push({
                                label: val,
                                value: key,
                                selected: $.inArray(key, selectedParkingList) !== -1
                            });
                        }
                    );
                    
                    parkingSelect.multiselect("dataprovider", data);
                    updateSelectedParking();
                }
           }
           
           updateParking();
           
           $("#passCategory").change(function () {
               updateParking();
           });
        ';
    }
}
