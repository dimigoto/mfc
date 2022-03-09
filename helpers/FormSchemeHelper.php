<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

use common\modules\mfc\MfcModule;
use common\modules\mfc\services\MfcRequestDataService;

/**
 * Вспомогательные функции для разбора схемы формы
 */
class FormSchemeHelper
{
    private array $structure;

    /**
     * @param array $structure Структура формы
     */
    public function __construct(array $structure)
    {
        $this->structure = $structure;
    }

    /**
     * Допустимые расширения загружаемого файла
     *
     * @param array $formElement
     *
     * @return array|null
     */
    public function getFileExtensions(array $formElement): ?array
    {
        $customFileExtensions = $this->getCustomFileExtensions($formElement);

        if ($customFileExtensions) {
            return $customFileExtensions;
        }

        return MfcRequestDataService::getAllowedFileExtensions();
    }

    /**
     * Индивидуальный для данного поля выбора файла набор допустимых расширений
     *
     * @param array $formElement
     *
     * @return array|null
     */
    public function getCustomFileExtensions(array $formElement): ?array
    {
        if (!empty($formElement['extensions'])) {
            return $formElement['extensions'];
        }

        return null;
    }

    /**
     * @param array $formElement
     *
     * @return array|null
     */
    public function getMaxWidth(array $formElement): ?array
    {
        if (!empty($formElement['maxWidth'])) {
            return $formElement['maxWidth'];
        }

        return null;
    }

    /**
     * Имя элемента формы
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string|null
     */
    public function getName(array $formElement): ?string
    {
        if (!empty($formElement['name'])) {
            return $formElement['name'];
        }

        return null;
    }

    /**
     * Формат входных данных для элемента формы
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string|null
     */
    public function getFormat(array $formElement): ?string
    {
        if (!empty($formElement['format'])) {
            return $formElement['format'];
        }

        return null;
    }

    /**
     * Спецификация элемента формы по его имени
     *
     * @param string $name Имя элемента формы
     * @param array|null $structure Структура формы
     *
     * @return array|null
     */
    public function getFormElementSchemeByName(string $name, array $structure = null): ?array
    {
        $_structure = $structure ?? $this->structure;

        foreach ($_structure as $item) {
            if ($this->getName($item) === $name) {
                return $item;
            }

            if ($this->isFieldset($item)) {
                $result = $this->getFormElementSchemeByName(
                    $name,
                    $this->getChildren($item)
                );

                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Проверка, является ли элемент формы заголовком
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isHeading(array $formElement): bool
    {
        return 'heading' === $formElement['class'];
    }

    /**
     * Проверка, является ли элемент формы справочным блоком
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isInfo(array $formElement): bool
    {
        return 'info' === $formElement['class'];
    }

    /**
     * Проверка, является ли элемент формы группой полей ввода
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isFieldset(array $formElement): bool
    {
        return 'fieldset' === $formElement['class'];
    }

    /**
     * Проверка, является ли элемент формы устаревшим
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isGhost(array $formElement): bool
    {
        return !empty($formElement['ghost']);
    }

    /**
     * Проверка, является ли элемент формы полем выбора файла
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isFile(array $formElement): bool
    {
        return 'file' === $formElement['class'];
    }

    /**
     * Проверка, является ли элемент формы полем выбора группы файлов
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isFileMultiple(array $formElement): bool
    {
        return $this->isFile($formElement)
            && isset($formElement['multiple'])
            && true === $formElement['multiple'];
    }

    /**
     * Проверка, является ли элемент формы полем ввода
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isField(array $formElement): bool
    {
        return 'field' === $formElement['class'];
    }

    /**
     * Проверка, есть ли у элемента формы подсказка
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function hasHint(array $formElement): bool
    {
        return !empty($formElement['hint']);
    }

    /**
     * Подсказка к полю ввода
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string|null
     */
    public function getHint(array $formElement): ?string
    {
        if ($this->hasHint($formElement)) {
            return MfcModule::t('common', $formElement['hint']);
        }

        return null;
    }

    /**
     * Проверка, есть ли у элемента формы название для отображения в разделе "Мои заявки"
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function hasTitle(array $formElement): bool
    {
        return !empty($formElement['title']);
    }

    /**
     * Название для отображения в разделе "Мои заявки"
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string|null
     */
    public function getTitle(array $formElement): ?string
    {
        if ($this->hasTitle($formElement)) {
            return MfcModule::t('common', $formElement['title']);
        }

        return null;
    }

    /**
     * Проверка, есть ли у элемента формы подпись
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function hasLabel(array $formElement): bool
    {
        return !empty($formElement['label']);
    }

    /**
     * Подпись для элемента формы
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string|null
     */
    public function getLabel(array $formElement): ?string
    {
        if ($this->hasLabel($formElement)) {
            return MfcModule::t('common', $formElement['label']);
        }

        return null;
    }

    /**
     * Проверка, имеет ли элемент формы дочерние элементы
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function hasChildren(array $formElement): bool
    {
        return !empty($formElement['children']);
    }

    /**
     * Дочерние элементы
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return array|null
     */
    public function getChildren(array $formElement): ?array
    {
        if ($this->hasChildren($formElement)) {
            return $formElement['children'];
        }

        return null;
    }

    /**
     * Проверка, является ли элемент формы клонируемым
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isMultiple(array $formElement): bool
    {
        return !empty($formElement['multiple']);
    }

    /**
     * Является поле ввода заблокированным для редактирования
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isReadOnly(array $formElement): bool
    {
        return !empty($formElement['readonly']);
    }

    /**
     * Является поле ввода обязательным для заполнения
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function isRequired(array $formElement): bool
    {
        return !empty($formElement['required']);
    }

    /**
     * Проверка, есть ли у элемента формы правила включения
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function hasEnabledRules(array $formElement): bool
    {
        return !empty($formElement['enabledRules'])
            && !empty($formElement['enabledRules']['from']);
    }

    /**
     * Проверка, есть ли у элемента формы правила включения,
     * зависящие от конкретного выбранного значения
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return bool
     */
    public function hasEnabledRulesFromValue(array $formElement): bool
    {
        return !empty($formElement['enabledRules'])
            && !empty($formElement['enabledRules']['value']);
    }

    /**
     * Имя поля ввода, от которого зависит включление элемента формы
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string|null
     */
    public function getEnabledFrom(array $formElement): ?string
    {
        if ($this->hasEnabledRules($formElement)) {
            return $formElement['enabledRules']['from'];
        }

        return null;
    }

    /**
     * Значение поля ввода, от которого зависит включление элемента формы
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string|null
     */
    public function getEnabledValue(array $formElement): ?string
    {
        if ($this->hasEnabledRules($formElement)) {
            return $formElement['enabledRules']['value'];
        }

        return null;
    }

    /**
     * Считыватель сохранённого значения поля ввода
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string
     */
    public function getReaderType(array $formElement): string
    {
        return $formElement['read'];
    }
}
