<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

use common\modules\mfc\factories\ReaderFactory;

/**
 * Считыватель сохранённой форм
 */
class FormContentReaderHelper
{
    private FormSchemeHelper $formSchemeHelper;
    private ReaderFactory $readerFactory;
    private array $data;
    private array $structure;
    private int $enquiryId;

    /**
     * @param array $data Данные
     * @param array $structure Структура
     * @param int $enquiryId ID заявки
     * @param FormSchemeHelper $formSchemeHelper
     */
    public function __construct(
        array $data,
        array $structure,
        int $enquiryId,
        FormSchemeHelper $formSchemeHelper
    ) {
        $this->readerFactory = new ReaderFactory();
        $this->formSchemeHelper = $formSchemeHelper;
        $this->data = $data;
        $this->structure = $structure;
        $this->enquiryId = $enquiryId;

        //dd($this->data);
    }

    /**
     * Считывание сохранённых данных формы
     *
     * @return array
     */
    public function readSavedData(): array
    {
        return $this->getElements($this->structure);
    }

    /**
     * Считывание данных из набора элементов формы
     *
     * @param array $formElements Элементы формы
     *
     * @return array
     */
    private function getElements(array $formElements): array
    {
        $result = [];

        foreach ($formElements as $formElement) {
            $formElementName = $this->formSchemeHelper->getName($formElement);

            if ($this->formSchemeHelper->isFieldset($formElement)) {
                if ($this->formSchemeHelper->isMultiple($formElement)) {
                    if (!isset($this->data[$formElementName])) {
                        continue;
                    }

                    $children = $this->data[$formElementName];

                    if (!is_array($children)) {
                        continue;
                    }

                    $label = '';

                    if ($this->formSchemeHelper->hasTitle($formElement)) {
                        $label = $this->formSchemeHelper->getTitle($formElement);
                    }

                    if ($this->formSchemeHelper->hasLabel($formElement)) {
                        $label = $this->formSchemeHelper->getLabel($formElement);
                    }

                    $result[$formElementName] = [
                        'value' => $this->getMultipleElements(
                            $this->formSchemeHelper->getChildren($formElement),
                            $children
                        ),
                        'label' => $label,
                        'format' => 'html',
                    ];
                } else {
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $result = array_merge(
                        $result,
                        $this->getElements(
                            $this->formSchemeHelper->getChildren($formElement)
                        )
                    );
                }

                continue;
            }

            if (!isset($this->data[$formElementName])) {
                continue;
            }

            if ($formElement['read'] === ReaderFactory::NONE_READER) {
                continue;
            }

            $result[$formElementName] = [
                'value' => $this->readValue(
                    $formElement,
                    $this->data[$formElementName]
                ),
                'label' => $this->formSchemeHelper->getLabel($formElement),
                'format' => $this->getFormat($formElement),
            ];
        }

        return $result;
    }

    /**
     * Формат вывода
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string
     */
    private function getFormat(array $formElement): string
    {
        /*if ($this->formSchemeHelper->isFile($formElement)) {
            return 'html';
        }*/

        return 'html';
    }

    /**
     * Значение клонируемой группы полей ввода
     *
     * @param array $structure Схема
     * @param array $children Данные
     *
     * @return string
     */
    private function getMultipleElements(
        array $structure,
        array $children
    ): string {
        $result = [];
        $counter = 0;

        foreach ($children as $child) {
            if (count($children) > 1) {
                $result[] = sprintf('<b>№ %s</b>', $counter + 1);
            }

            foreach ($child as $attributeName => $attributeValue) {
                $formElement = $this->formSchemeHelper->getFormElementSchemeByName(
                    $attributeName,
                    $structure
                );

                if (empty($formElement)) {
                    continue;
                }

                $label = $this->formSchemeHelper->getLabel($formElement);
                $value = $this->readValue(
                    $formElement,
                    $attributeValue,
                    ['number' => $counter + 1]
                );

                if (count($child) > 1) {
                    $result[] = sprintf('<dl><dt>%s</dt><dd>%s</dd></dl>', $label, $value);
                } else {
                    $result[] = sprintf('<p>%s</p>', $value);
                }
            }

            $counter++;
        }

        $result = implode('', $result);

        return $result;
    }

    /**
     * Считывание данных элемента формы
     *
     * @param array $formElement Спецификация элемента формы
     * @param mixed $value Сохранённое значение поля ввода
     * @param array $options
     *
     * @return string
     */
    private function readValue(array $formElement, $value, array $options = []): string
    {
        $reader = $this->readerFactory->createReader(
            $this->formSchemeHelper->getReaderType($formElement)
        );

        if ($this->formSchemeHelper->isFile($formElement)) {
            $_options = $options;
            $_options['enquiryId'] = $this->enquiryId;

            return $reader->readValue(
                $value,
                $formElement,
                $_options
            );
        }

        if (is_array($value)) {
            $_value = [];

            foreach ($value as $item) {
                $_value[] = $reader->readValue($item, $formElement, []);
            }

            return implode('<br>', $_value);
        }

        return $reader->readValue($value, $formElement, []);
    }
}
