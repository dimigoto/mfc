<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

use common\modules\mfc\factories\FormFieldRendererFactory;
use common\modules\mfc\factories\FormFieldsetRendererFactory;
use common\modules\mfc\factories\FormHeadingElementRendererFactory;
use common\modules\mfc\factories\FormInfoElementRendererFactory;
use common\modules\mfc\interfaces\FormElementRendererFactoryInterface;
use common\modules\mfc\interfaces\FormFieldsetRendererFactoryInterface;
use common\modules\userUniver\models\User;
use Exception;
use yii\bootstrap4\Html;

/**
 * Генератор форм
 */
class FormContentRenderHelper
{
    private FormFieldRendererFactory $fieldRendererFactory;
    private FormHeadingElementRendererFactory $headingRendererFactory;
    private FormInfoElementRendererFactory $infoRendererFactory;
    private FormFieldsetRendererFactory $fieldsetRendererFactory;
    private FormElementRenderHelper $fieldRenderHelper;
    private FormSchemeHelper $formSchemeHelper;
    private string $customJavascript = '';

    public function __construct(User $user, FormSchemeHelper $formSchemeHelper)
    {
        $this->fieldRendererFactory = new FormFieldRendererFactory($user);
        $this->headingRendererFactory = new FormHeadingElementRendererFactory($user);
        $this->infoRendererFactory = new FormInfoElementRendererFactory($user);
        $this->fieldsetRendererFactory = new FormFieldsetRendererFactory($user);
        $this->fieldRenderHelper = new FormElementRenderHelper($formSchemeHelper);
        $this->formSchemeHelper = $formSchemeHelper;
    }

    /**
     * Генерация содержимого формы
     *
     * @param array $formStructure Структура формы
     * @param string $enquiryTypeId
     *
     * @return string
     * @throws Exception
     */
    public function render(array $formStructure, string $enquiryTypeId): string
    {
        $result = '';
        $result .= $this->renderElements($formStructure);

        $result .= Html::hiddenInput('Enquiry[type]', $enquiryTypeId);

        return $result;
    }

    /**
     * Javascript для динамических правил формы
     *
     * @return string
     */
    public function getCustomJavascript(): string
    {
        return $this->customJavascript;
    }

    /**
     * Генерация элементов формы по заданной структуре
     *
     * @param array $structure Структура
     * @param bool $isInMultiple
     *
     * @return string
     * @throws Exception
     */
    public function renderElements(array $structure, bool $isInMultiple = false): string
    {
        $result = '';

        foreach ($structure as $formElement) {
            if ($this->formSchemeHelper->isGhost($formElement)) {
                continue;
            }

            if ($this->formSchemeHelper->isFieldset($formElement)) {
                $children = '';

                if ($this->formSchemeHelper->hasLabel($formElement)) {
                    $children .= Html::tag(
                        'p',
                        $this->formSchemeHelper->getLabel($formElement)
                    );
                }

                $customRules = [];

                if (!empty($formElement['type'])) {
                    $factory = $this->getElementRendererFactory($formElement);
                    $renderer = $factory->createRenderer(
                        $formElement['type'],
                        $this->formSchemeHelper
                    );
                    $children .= $renderer->render($formElement);
                    $this->customJavascript .= $renderer->getCustomJavascript();

                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $customRules = array_merge($customRules, $renderer->getCustomRules());
                } elseif ($this->formSchemeHelper->hasChildren($formElement)) {
                    $children .= $this->renderElements(
                        $this->formSchemeHelper->getChildren($formElement),
                        $this->formSchemeHelper->isMultiple($formElement)
                    );
                }

                if ($this->formSchemeHelper->isMultiple($formElement)) {
                    $children .= CustomHtml::tag(
                        'button',
                        '+',
                        ['class' => 'repeat_append_button']
                    );
                    $children .= CustomHtml::tag(
                        'button',
                        '&minus;',
                        ['class' => 'repeat_remove_button']
                    );
                    $customRules['name'] = sprintf('\'%s\'', $formElement['name']);

                    if (empty($customRules['repeat'])) {
                        $customRules['repeat'] = 'true';
                    }
                }

                $options = $this->fieldRenderHelper->getOptions(
                    $formElement,
                    $customRules,
                    $isInMultiple
                );

                if (empty($options['class'])) {
                    $cssClass = [];
                } else {
                    $cssClass = [$options['class']];
                }

                $cssClass[] = 'fields';
                $options['class'] = implode(' ', $cssClass);

                $result .= CustomHtml::tag('fieldset', $children, $options);
            } else {
                $factory = $this->getElementRendererFactory($formElement);
                $renderer = $factory->createRenderer($formElement['type'], $this->formSchemeHelper);
                $result .= $renderer->render($formElement);
                $this->customJavascript .= $renderer->getCustomJavascript();
            }
        }

        return $result;
    }

    /**
     * Выбор фабрики для создания визуализаторов
     *
     * @param array $formElement Спецификация элемента формы
     *
     * @return FormElementRendererFactoryInterface|FormFieldsetRendererFactoryInterface
     */
    private function getElementRendererFactory(array $formElement)
    {
        if ($this->formSchemeHelper->isInfo($formElement)) {
            return $this->infoRendererFactory;
        }

        if ($this->formSchemeHelper->isHeading($formElement)) {
            return $this->headingRendererFactory;
        }

        if ($this->formSchemeHelper->isFieldset($formElement)) {
            return $this->fieldsetRendererFactory;
        }

        return $this->fieldRendererFactory;
    }
}
