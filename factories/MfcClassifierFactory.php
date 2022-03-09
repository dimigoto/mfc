<?php

declare(strict_types=1);

namespace common\modules\mfc\factories;

use common\modules\mfc\interfaces\MenuItemInterface;
use common\modules\mfc\models\MfcCategory;
use common\modules\mfc\models\MfcEnquiryType;
use common\modules\mfc\models\MfcLink;
use common\modules\mfc\models\MfcMenuItemData;
use RuntimeException;

/**
 * Фабрика для категорий и заявок
 */
class MfcClassifierFactory
{
    /**
     * @param MfcMenuItemData $data
     *
     * @return MenuItemInterface
     */
    public function createMfcElement(MfcMenuItemData $data): MenuItemInterface
    {
        if ($data->isCategory()) {
            return $this->createMfcCategory(
                $data->getId(),
                $data->getRoles()
            );
        }

        if ($data->isEnquiry()) {
            return $this->createMfcEnquiryType(
                $data->getId(),
                $data->getAlias(),
                $data->isArchive(),
                $data->getRoles()
            );
        }

        if ($data->isLink()) {
            return $this->createMfcLink(
                $data->getId(),
                $data->getUrl(),
                $data->getRoles()
            );
        }

        throw new RuntimeException('Не определён тип элемента');
    }

    /**
     * Создание объекта для внешней ссылки
     *
     * @param string $id
     * @param string $url
     * @param array|null $roles
     *
     * @return MfcLink
     */
    public function createMfcLink(string $id, string $url, ?array $roles = []): MfcLink
    {
        return new MfcLink($id, $url, $roles);
    }

    /**
     * Создание объекта категории
     *
     * @param string $id
     * @param array|null $roles
     *
     * @return MfcCategory
     */
    public function createMfcCategory(string $id, ?array $roles = []): MfcCategory
    {
        return new MfcCategory($id, $roles);
    }

    /**
     * Создание объекта заявки
     *
     * @param string $id
     * @param string|null $alias
     * @param bool $isArchive
     * @param array|null $roles
     *
     * @return MfcEnquiryType
     */
    public function createMfcEnquiryType(
        string $id,
        ?string $alias,
        bool $isArchive = false,
        ?array $roles = []
    ): MfcEnquiryType {
        return new MfcEnquiryType($id, $alias, $isArchive, $roles);
    }
}
