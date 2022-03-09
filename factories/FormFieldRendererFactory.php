<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\helpers\renderers\AcceptFieldRenderer;
use common\modules\mfc\helpers\renderers\AccommodationFieldRenderer;
use common\modules\mfc\helpers\renderers\AddressFieldRenderer;
use common\modules\mfc\helpers\renderers\BirthdayFieldRenderer;
use common\modules\mfc\helpers\renderers\BuildingsFieldRenderer;
use common\modules\mfc\helpers\renderers\CarParkingFieldRenderer;
use common\modules\mfc\helpers\renderers\CarPassCategoryFieldRenderer;
use common\modules\mfc\helpers\renderers\CheckboxFieldRenderer;
use common\modules\mfc\helpers\renderers\CorporateEmailFieldRenderer;
use common\modules\mfc\helpers\renderers\CountFieldRenderer;
use common\modules\mfc\helpers\renderers\DateFieldRenderer;
use common\modules\mfc\helpers\renderers\DateTimeFieldRenderer;
use common\modules\mfc\helpers\renderers\EducationProgramFieldRenderer;
use common\modules\mfc\helpers\renderers\EmployeePostFieldRenderer;
use common\modules\mfc\helpers\renderers\FileFieldRenderer;
use common\modules\mfc\helpers\renderers\FullNameFieldRenderer;
use common\modules\mfc\helpers\renderers\HiddenFieldRenderer;
use common\modules\mfc\helpers\renderers\PassportFieldRenderer;
use common\modules\mfc\helpers\renderers\PhoneFieldRenderer;
use common\modules\mfc\helpers\renderers\PreviousEducationDocumentFieldRenderer;
use common\modules\mfc\helpers\renderers\RadioFieldRenderer;
use common\modules\mfc\helpers\renderers\SelectFieldRenderer;
use common\modules\mfc\helpers\renderers\TextareaFieldRenderer;
use common\modules\mfc\helpers\renderers\TextFieldRenderer;
use common\modules\mfc\helpers\renderers\YearsIntervalFieldRenderer;
use common\modules\mfc\interfaces\FormElementRendererInterface;

/**
 * Фабрика генераторов полей ввода форм
 */
class FormFieldRendererFactory extends BaseFormElementRendererFactory
{
    public const ACCEPT_TYPE = 'accept';
    public const ACCOMMODATION_TYPE = 'accommodation';
    public const ADDRESS_TYPE = 'address';
    public const BIRTHDAY_TYPE = 'birthday';
    public const BUILDINGS_TYPE = 'buildings';
    public const CAR_PASS_CATEGORY_TYPE = 'carPassCategory';
    public const CHECKBOX_TYPE = 'checkbox';
    public const CORPORATE_EMAIL_TYPE = 'corporateEmail';
    public const COUNT_TYPE = 'count';
    public const DATE_TYPE = 'date';
    public const DATE_TIME_TYPE = 'datetime';
    public const EDUCATION_PROGRAM_TYPE = 'educationProgram';
    public const EMPLOYEE_POST_TYPE = 'employeePost';
    public const FILE_TYPE = 'file';
    public const FULL_NAME_TYPE = 'fullName';
    public const HIDDEN_TYPE = 'hidden';
    public const PARKING_TYPE = 'parking';
    public const PASSPORT_TYPE = 'passport';
    public const PHONE_TYPE = 'phone';
    public const PREVIOUS_EDUCATION_DOCUMENT_TYPE = 'previousEducationDocument';
    public const RADIO_TYPE = 'radio';
    public const SELECT_TYPE = 'select';
    public const TEXTAREA_TYPE = 'textarea';
    public const YEARS_INTERVAL_TYPE = 'yearsInterval';

    /**
     * {@inheritdoc}
     */
    public function createRenderer(string $type, FormSchemeHelper $formSchemeHelper): FormElementRendererInterface
    {
        switch ($type) {
            case self::ACCEPT_TYPE:
                return new AcceptFieldRenderer($formSchemeHelper);
            case self::ACCOMMODATION_TYPE:
                return new AccommodationFieldRenderer($formSchemeHelper, $this->user);
            case self::ADDRESS_TYPE:
                return new AddressFieldRenderer($formSchemeHelper, $this->user);
            case self::BIRTHDAY_TYPE:
                return new BirthdayFieldRenderer($formSchemeHelper, $this->user);
            case self::BUILDINGS_TYPE:
                return new BuildingsFieldRenderer($formSchemeHelper, $this->user);
            case self::CAR_PASS_CATEGORY_TYPE:
                return new CarPassCategoryFieldRenderer($formSchemeHelper, $this->user);
            case self::CHECKBOX_TYPE:
                return new CheckboxFieldRenderer($formSchemeHelper);
            case self::CORPORATE_EMAIL_TYPE:
                return new CorporateEmailFieldRenderer($formSchemeHelper, $this->user);
            case self::COUNT_TYPE:
                return new CountFieldRenderer($formSchemeHelper);
            case self::DATE_TYPE:
                return new DateFieldRenderer($formSchemeHelper);
            case self::DATE_TIME_TYPE:
                return new DateTimeFieldRenderer($formSchemeHelper);
            case self::EDUCATION_PROGRAM_TYPE:
                return new EducationProgramFieldRenderer($formSchemeHelper, $this->user);
            case self::EMPLOYEE_POST_TYPE:
                return new EmployeePostFieldRenderer($formSchemeHelper, $this->user);
            case self::FILE_TYPE:
                return new FileFieldRenderer($formSchemeHelper);
            case self::FULL_NAME_TYPE:
                return new FullNameFieldRenderer($formSchemeHelper, $this->user);
            case self::HIDDEN_TYPE:
                return new HiddenFieldRenderer($formSchemeHelper);
            case self::PARKING_TYPE:
                return new CarParkingFieldRenderer($formSchemeHelper, $this->user);
            case self::PASSPORT_TYPE:
                return new PassportFieldRenderer($formSchemeHelper, $this->user);
            case self::PHONE_TYPE:
                return new PhoneFieldRenderer($formSchemeHelper, $this->user);
            case self::PREVIOUS_EDUCATION_DOCUMENT_TYPE:
                return new PreviousEducationDocumentFieldRenderer($formSchemeHelper, $this->user);
            case self::RADIO_TYPE:
                return new RadioFieldRenderer($formSchemeHelper);
            case self::SELECT_TYPE:
                return new SelectFieldRenderer($formSchemeHelper);
            case self::TEXTAREA_TYPE:
                return new TextareaFieldRenderer($formSchemeHelper);
            case self::YEARS_INTERVAL_TYPE:
                return new YearsIntervalFieldRenderer($formSchemeHelper);
            default:
                return new TextFieldRenderer($formSchemeHelper);
        }
    }
}
