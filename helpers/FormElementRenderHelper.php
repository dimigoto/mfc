<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

use common\modules\mfc\MfcModule;
use common\modules\mfc\services\MfcRequestDataService;
use yii\helpers\Html;

/**
 * Вспомогательные функции для генерации элементов формы
 */
class FormElementRenderHelper
{
    private const ENABLED_RULE_DELIMITER = '|';

    private FormSchemeHelper $formSchemeHelper;

    public function __construct(FormSchemeHelper $formSchemeHelper)
    {
        $this->formSchemeHelper = $formSchemeHelper;
    }

    /**
     * Строка формы
     *
     * @param string $label Подпись
     * @param string $input Поле ввода
     * @param string $hint Подсказка
     * @param array|null $maxWidth Максимальная ширина поля ввода
     * @param array $options Параметры контейнера поля ввода
     *
     * @return string
     */
    public function renderFormRow(
        string $label,
        string $input,
        string $hint = '',
        array $maxWidth = null,
        array $options = []
    ): string {
        $cssClass = [];

        if (empty($maxWidth)) {
            $cssClass[] = 'col';
        } else {
            foreach ($maxWidth as $maxWidthItem) {
                $cssClass[] = sprintf(
                    'col-%s-%s',
                    $maxWidthItem['point'],
                    $maxWidthItem['size']
                );
            }
        }

        $cssClass = implode(' ', $cssClass);
        //$cssClass = 'col-lg-4 col-md-6';

        $result = '';

        $result .= $label;
        $result .= CustomHtml::tag(
            'div',
            CustomHtml::tag(
                'div',
                CustomHtml::tag(
                    'div',
                    $input,
                    ['class' => 'form-group']
                ),
                ['class' => $cssClass]
            ),
            ['class' => 'row']
        );

        $result .= $hint;

        $_options = $options;
        CustomHtml::addCssClass($_options, 'field');

        return CustomHtml::tag('div', $result, $_options);
    }

    /**
     * Имя поля ввода
     *
     * @param string $name Имя поля ввода из спецификации
     *
     * @return string
     */
    private function getEscapedFieldName(string $name): string
    {
        return str_replace(['[', ']'], ['\\\[', '\\\]'], $this->getFieldName($name));
    }

    /**
     * Имя поля ввода
     *
     * @param string $name Имя поля ввода из спецификации
     *
     * @return string
     */
    public function getFieldName(string $name): string
    {
        return sprintf('Enquiry[%s]', $name);
        //return $name;
    }

    /**
     * Подпись к полю ввода
     *
     * @param string $title Текст подписи к полю ввода
     * @param string $id ID поля ввода
     *
     * @return string
     */
    public function renderLabel(string $title, string $id): string
    {
        return Html::label($title, $id);
    }

    /**
     * Параметры тега
     *
     * @param array $formElement Спецификация элемента формы
     * @param array $customRules Специфичные для конкретного элемента формы правила валидации
     * @param bool $isInMultiple
     *
     * @return array
     */
    public function getOptions(array $formElement, array $customRules = [], bool $isInMultiple = false): array
    {
        $result = [];

        $result['id'] = $formElement['name'];

        if (isset($formElement['cssClass']) && $formElement['cssClass']) {
            $result['class'] = $formElement['cssClass'];
        }

        if ($this->formSchemeHelper->isReadOnly($formElement)) {
            $result['readonly'] = true;
        }

        if ($this->formSchemeHelper->isRequired($formElement)) {
            $result['required'] = 'required';
        }

        if ($this->formSchemeHelper->hasHint($formElement)) {
            $result['aria-describedby'] = $this->getHintId($formElement['name']);
        }

        $rules = $this->getRules($formElement, $customRules, $isInMultiple);

        if (!empty($rules)) {
            $result['data-xp'] = $rules;
        }

        return $result;
    }

    /**
     * Подсказка к полю ввода
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return string
     */
    public function renderHint(array $formElement): string
    {
        $result = '';

        if ($this->formSchemeHelper->hasHint($formElement)) {
            $result .= Html::tag(
                'p',
                $this->formSchemeHelper->getHint($formElement),
                [
                    'id' => $this->getHintId($formElement['name']),
                    'class' => 'form-text text-muted field__hint',
                ]
            );
        }

        if ($this->formSchemeHelper->isFile($formElement)) {
            $fileExtensions = $this->formSchemeHelper->getFileExtensions($formElement);

            $result .= Html::tag(
                'p',
                MfcModule::t(
                    'common',
                    'FILE_DOCUMENTS_HINT',
                    [
                        'max_file_size' => MfcRequestDataService::getMaxFileSizeMb(),
                        'file_extensions' => strtoupper(implode(', ', $fileExtensions)),
                    ]
                ),
                [
                    'class' => 'form-text text-muted field__hint',
                ]
            );
        }

        return $result;
    }

    /**
     * ID тега с подсказкой к полю ввода
     *
     * @param string $fieldId ID поля ввода
     *
     * @return string
     */
    protected function getHintId(string $fieldId): string
    {
        return $fieldId . 'Hint';
    }

    /**
     * Правила валидации и динамических преобразований
     *
     * @param array $formElement Спецификация
     * @param array $customRules Особые правила
     * @param bool $isInMultiple
     *
     * @return string
     */
    protected function getRules(array $formElement, array $customRules = [], bool $isInMultiple = false): string
    {
        $result = [];
        $rules = $customRules;

        if (!isset($rules['required']) && $this->formSchemeHelper->isRequired($formElement)) {
            $rules['required'] = 'true';
        }

        if ($this->formSchemeHelper->hasEnabledRules($formElement)) {
            $rules['enabled'] = $this->getEnabledRules($formElement, $isInMultiple);
        }

        foreach ($rules as $key => $val) {
            $result[] = sprintf('%s: %s', $key, $val);
        }

        return implode(', ', $result);
    }

    /**
     * Правила включения
     *
     * @param array $formElement Спецификация
     * @param bool $isInMultiple
     *
     * @return string
     */
    protected function getEnabledRules(array $formElement, bool $isInMultiple = false): string
    {
        $from = $this->formSchemeHelper->getFormElementSchemeByName(
            $this->formSchemeHelper->getEnabledFrom($formElement)
        );

        if ($this->formSchemeHelper->hasEnabledRulesFromValue($formElement)) {
            if ('radio' === $from['type']) {
                if ($isInMultiple) {
                    $format = '[name=%s\\\[0\\\]][value=%s]';
                } else {
                    $format = '[name=%s][value=%s]';
                }
            } else {
                $format = '[name=%s]==\\\'%s\\\'';
            }

            $enabledValue = $this->formSchemeHelper->getEnabledValue($formElement);

            if (strpos($enabledValue, self::ENABLED_RULE_DELIMITER) !== false) {
                $enabledValues = explode(self::ENABLED_RULE_DELIMITER, $enabledValue);
            } else {
                $enabledValues = [$enabledValue];
            }

            $escapedFieldName = $this->getEscapedFieldName(
                $this->formSchemeHelper->getEnabledFrom($formElement)
            );

            $items = [];

            foreach ($enabledValues as $enabledValue) {
                $items[] = sprintf($format, $escapedFieldName, $enabledValue);
            }

            return sprintf('\'%s\'', implode(' || ', $items));
        }

        return sprintf(
            '\'[name=%s]\'',
            $this->getEscapedFieldName(
                $this->formSchemeHelper->getEnabledFrom($formElement)
            )
        );
    }
}
