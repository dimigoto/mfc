<?php

namespace common\modules\mfc\interfaces;

/**
 * Интерфейс элемента меню
 */
interface MenuItemInterface
{
    /**
     * ID
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Текст ссылки
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Подсказка для ссылки
     *
     * @return string
     */
    public function getHint(): string;

    /**
     * Адрес ссылки
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     *  Получить иконку сущности.
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Получить массив ролей.
     *
     * @return  string[]
     */
    public function getRoles(): array;

    /**
     * Получение юрл для сущности SearchMfc.ы
     *
     * @return string
     */
    public function getUrlForSearchMfc(): string;
}
