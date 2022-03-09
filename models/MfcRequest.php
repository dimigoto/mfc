<?php

declare(strict_types=1);

namespace common\modules\mfc\models;

use common\Helpers\JsonHelper;
use common\modules\mfc\factories\MfcElementsRepositoryFactory;
use common\modules\mfc\helpers\FormContentReaderHelper;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\MfcModule;
use common\modules\mfc\repositories\MfcElementsRepository;
use common\modules\userRequest\models\UserRequest;

/**
 * Модель заявки ИТИЛ
 *
 * @property int|null $request_id ID базовой заявки
 * @property string|null $subtype Тип заявки
 * @property string|null $data Содержимое заявки
 *
 * @property-read string|null $typeDisplay
 * @property-read MfcEnquiryType $enquiryType
 */
class MfcRequest extends UserRequest
{
    public const TYPE = 'MFC';

    public ?int $request_id = null;
    public ?string $subtype = null;
    public ?string $data = null;

    private MfcElementsRepositoryFactory $mfcElementsRepositoryFactory;

    private array $customAttributesDisplay = [];
    private array $customAttributesLabels = [];
    private array $customAttributes = [];

    /**
     * Проверка, относится ли файл с заданным хешем к заявке
     *
     * @param string $hash Хеш файла
     *
     * @return bool
     */
    public function hasFile(string $hash): bool
    {
        return false !== strpos($this->data, $hash);
    }

    /**
     * Информация о файле
     *
     * @param string $hash Хеш файла
     *
     * @return array|null
     */
    public function getFileInfo(string $hash): ?array
    {
        return $this->findFileInfoInData(
            JsonHelper::jsonDecode($this->data),
            $hash
        );
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->mfcElementsRepositoryFactory = new MfcElementsRepositoryFactory();

        $this->addCustomAttributeLabel(
            'typeDisplay',
            MfcModule::t('common', 'ENQUIRY_TYPE')
        );
        $this->addCustomAttributeLabel(
            'dataDisplay',
            MfcModule::t('common', 'ENQUIRY_DATA')
        );
        $this->addCustomAttributeLabel(
            'attempts',
            MfcModule::t('common', 'ATTEMPTS')
        );

        $this->on(
            'onRetrieve',
            static function ($event) {
                /** @var MfcRequest $sender */
                $sender = $event->sender;
                $enquiryType = $sender->getEnquiryType();

                $formSchemeHelper = new FormSchemeHelper($enquiryType->getStructure());

                $formContentReaderHelper = new FormContentReaderHelper(
                    JsonHelper::jsonDecode($sender->data),
                    $enquiryType->getStructure(),
                    $sender->id,
                    $formSchemeHelper
                );

                $dataParsed = $formContentReaderHelper->readSavedData();

                foreach ($dataParsed as $attributeName => $attributeContent) {
                    if (isset($attributeContent['format'])) {
                        $sender->addCustomAttributesDisplay([
                            'attribute' => $attributeName,
                            'format' => $attributeContent['format'],
                        ]);
                    } else {
                        $sender->addCustomAttributesDisplay($attributeName);
                    }

                    $sender->addCustomAttributeLabel(
                        $attributeName,
                        $attributeContent['label']
                    );
                    $sender->setCustomAttribute(
                        $attributeName,
                        $attributeContent['value']
                    );
                }
            }
        );
    }

    /**
     * Номер заявки для отображения
     *
     * @return string|null
     */
    public function getNumberDisplay(): ?string
    {
        $number = parent::getNumberDisplay();
        $result = explode('-', (string)$number);

        if (1 === count($result)) {
            return $number;
        }

        $result[1] = ltrim($result[1], '0');
        $result = implode('-', $result);

        return $result;
    }

    /**
     * Тип заявки для фильтра
     *
     * @return string
     */
    public function getTypeForFilter(): string
    {
        return sprintf('%s:%s', parent::getTypeForFilter(), $this->subtype);
    }

    /**
     * Заявка имеет подтипы
     *
     * @return bool
     */
    public function hasSubtype(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            array_keys($this->customAttributes)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->customAttributes)) {
            return $this->customAttributes[$name];
        }

        return parent::__get($name);
    }

    /**
     * @param string $attributeName Имя атрибута
     * @param mixed $attributeValue Значение атрибута
     */
    public function setCustomAttribute(string $attributeName, $attributeValue): void
    {
        $this->customAttributes[$attributeName] = $attributeValue;
    }

    /**
     * Тип заявки
     *
     * @return MfcEnquiryType
     */
    public function getEnquiryType(): MfcEnquiryType
    {
        $mfcElementsRepository = $this->mfcElementsRepositoryFactory->createRepository(null, true);

        return $mfcElementsRepository->findOneEnquiryById($this->subtype);
    }

    /**
     * Добавление параметра
     *
     * @param mixed $attributeName Атрибут
     */
    public function addCustomAttributesDisplay($attributeName): void
    {
        $this->customAttributesDisplay[] = $attributeName;
    }

    /**
     * Добавление параметра
     *
     * @param string $attributeName Имя атрибута
     * @param string|null $label Подпись
     */
    public function addCustomAttributeLabel(string $attributeName, ?string $label): void
    {
        $this->customAttributesLabels[$attributeName] = $label;
    }

    /**
     * {@inheritdoc}
     */
    public function hasForeignNumber(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameDisplay(): string
    {
        return $this->getTypeDisplay();

        //return MfcModule::t('common', 'REQUEST_TITLE');

        /*return sprintf(
            '%s: %s',
            MfcModule::t('common', 'REQUEST_TITLE'),
            $this->getTypeDisplay()
        );*/
    }

    /**
     * Тип заявки для отображения
     *
     * @return string|null
     */
    public function getTypeDisplay(): string
    {
        return $this->getEnquiryType()->getTitle();
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomAttributesLabels(): array
    {
        return $this->customAttributesLabels;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomAttributesDisplay(): array
    {
        return $this->customAttributesDisplay;
    }

    /**
     * Поиск информации о файле среди сохранённых данных
     *
     * @param array $dataDecoded Данные
     * @param string $hash Хеш файла
     *
     * @return array|null
     */
    private function findFileInfoInData(array $dataDecoded, string $hash): ?array
    {
        foreach ($dataDecoded as $dataItem) {
            if (!is_array($dataItem)) {
                continue;
            }

            if (isset($dataItem['hash'])) {
                if ($hash === $dataItem['hash']) {
                    return $dataItem;
                }
            } else {
                $found = $this->findFileInfoInData($dataItem, $hash);

                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }
}
