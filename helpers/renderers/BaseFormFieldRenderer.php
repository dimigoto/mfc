<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\renderers;

use common\modules\mfc\helpers\FormElementRenderHelper;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\interfaces\FormElementRendererInterface;
use common\modules\mfc\MfcModule;

/**
 * Генератор элемента формы
 */
abstract class BaseFormFieldRenderer implements FormElementRendererInterface
{
    protected FormElementRenderHelper $fieldRenderHelper;
    protected FormSchemeHelper $formSchemeHelper;
    protected string $cssClassDefault = 'form-control';

    public function __construct(FormSchemeHelper $formSchemeHelper)
    {
        $this->formSchemeHelper = $formSchemeHelper;
        $this->fieldRenderHelper = new FormElementRenderHelper($formSchemeHelper);
    }

    /**
     * Параметры контейнера поля ввода
     *
     * @param array $specification Спецификация
     *
     * @return array
     */
    public function getContainerOptions(array $specification): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $specification): string
    {
        if ($this->needSkip($specification)) {
            return '';
        }

        if ($this->isHidden($specification)) {
            return $this->renderInput($specification);
        }

        return $this->fieldRenderHelper->renderFormRow(
            $this->renderLabel($specification),
            $this->renderInput($specification),
            $this->fieldRenderHelper->renderHint($specification),
            $this->formSchemeHelper->getMaxWidth($specification),
            $this->getContainerOptions($specification)
        );
    }

    /**
     * Специфичный Javascript
     *
     * @return string
     */
    public function getCustomJavascript(): string
    {
        return '';
    }

    /**
     * Имя поля ввода
     *
     * @param string $name Имя поля ввода из спецификации
     *
     * @return string
     */
    protected function getFieldName(string $name): string
    {
        return $this->fieldRenderHelper->getFieldName($name);
    }

    /**
     * Тег подписи к полю ввода
     *
     * @param array $specification Спецификация
     *
     * @return string
     */
    protected function renderLabel(array $specification): string
    {
        if (empty($specification['label'])) {
            return '';
        }

        return $this->fieldRenderHelper->renderLabel(
            MfcModule::t('common', $specification['label']),
            $specification['name']
        );
    }

    /**
     * Подпись к полю ввода
     *
     * @param array $specification Спецификация
     *
     * @return string
     */
    protected function getLabel(array $specification): string
    {
        return MfcModule::t('common', $specification['label']);
    }

    /**
     * Параметры тега поля ввода
     *
     * @param array $specification Спецификация
     * @param array $customRules
     *
     * @return array
     */
    protected function getOptions(array $specification, array $customRules = []): array
    {
        $result = $this->fieldRenderHelper->getOptions($specification, $customRules);

        $cssClass = [];

        if (!empty($result['class'])) {
            $cssClass[] = $result['class'];
        }

        if ($this->formSchemeHelper->isReadOnly($specification)) {
            $cssClass[] = 'readonly';
        }

        if (!empty($this->cssClassDefault)) {
            $cssClass[] = $this->cssClassDefault;
        }

        $result['class'] = implode(' ', $cssClass);

        return $result;
    }

    /**
     * Выполнены условия, при которых нужно пропустить набор полей
     *
     * @param array $specification
     *
     * @return bool
     */
    protected function needSkip(array $specification): bool
    {
        return false;
    }

    /**
     * Выполнены условия, при которых набор полей скрыт от пользователя
     *
     * @param array $specification
     *
     * @return bool
     */
    protected function isHidden(array $specification): bool
    {
        return isset($specification['isHidden']) && true === $specification['isHidden'];
    }

    /**
     * Пункты
     *
     * @param array $specification Спецификация
     *
     * @return array
     */
    protected function getItems(array $specification): array
    {
        if (!empty($specification['itemsAsIs'])) {
            return $specification['itemsAsIs'];
        }

        $result = [];

        if (
            !empty($specification['items'])
            && is_array($specification['items'])
            && !empty($specification['label'])
        ) {
            foreach ($specification['items'] as $item) {
                $key = ('DEFAULT' === $item) ? '' : $item;

                $result[$key] = MfcModule::t(
                    'common',
                    sprintf(
                        '%s_ITEMS_%s',
                        $specification['label'],
                        $item
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Поле ввода
     *
     * @param array $specification Спецификация
     *
     * @return string
     */
    abstract public function renderInput(array $specification): string;
}
