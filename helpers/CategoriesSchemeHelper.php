<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

use common\modules\mfc\models\MfcMenuItemData;

/**
 * Вспомогательные функции для разбора схемы структуры категорий
 */
class CategoriesSchemeHelper
{
    private array $structure;
    private array $roles;
    private bool $withArchive;
    private bool $withRoles;

    /**
     * @param array $roles Роли пользователя
     * @param array $structure Структура формы
     * @param bool $withArchive
     * @param bool $withRoles
     */
    public function __construct(array $roles, array $structure, bool $withArchive = false, bool $withRoles = true)
    {
        $this->roles = $roles;
        $this->structure = $structure;
        $this->withArchive = $withArchive;
        $this->withRoles = $withRoles;
    }

    /**
     * @param string|null $parentId
     *
     * @return MfcMenuItemData[]
     */
    public function findAllChildren(?string $parentId): array
    {
        $result = [];

        $items = $this->findAll(['parent' => $parentId]);

        foreach ($items as $item) {
            if (
                !$item->isCategory()
                || !empty($this->findAllChildren($item->getId()))
            ) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param string $id
     *
     * @return MfcMenuItemData|null
     */
    public function findOneCategoryById(string $id): ?MfcMenuItemData
    {
        return $this->findOneByClassAndId(MfcMenuItemData::CLASS_CATEGORY, $id);
    }

    /**
     * @param string $id
     *
     * @return MfcMenuItemData|null
     */
    public function findOneEnquiryById(string $id): ?MfcMenuItemData
    {
        return $this->findOneByClassAndId(MfcMenuItemData::CLASS_ENQUIRY, $id);
    }

    /**
     * @param string $class
     * @param string $id
     *
     * @return MfcMenuItemData
     */
    private function findOneByClassAndId(string $class, string $id): ?MfcMenuItemData
    {
        $result = $this->findAll([
            'class' => $class,
            'id' => $id,
        ]);

        if (!empty($result)) {
            return $result[0];
        }

        return null;
    }

    /**
     * @param array $params
     *
     * @return MfcMenuItemData[]
     */
    private function findAll(array $params): array
    {
        $roles = $this->roles;
        $withArchive = $this->withArchive;
        $withRoles = $this->withRoles;

        $filtered = array_filter(
            $this->structure,
            static function (array $item) use ($params, $roles, $withArchive, $withRoles) {
                $isVisible = $withArchive || !isset($item['archive']) || false === $item['archive'];

                if (!$isVisible) {
                    return false;
                }

                $isPermitted = !$withRoles
                    || empty($item['roles'])
                    || !empty(array_intersect($item['roles'], $roles));

                if (!$isPermitted) {
                    return false;
                }

                $isValid = array_reduce(
                    array_keys($params),
                    static function (bool $r, string $key) use ($params, $item) {
                        return $r && $params[$key] === $item[$key];
                    },
                    true
                );

                return $isValid;
            }
        );

        $filtered = array_values($filtered);

        return array_map(
            static function (array $item) {
                return new MfcMenuItemData(
                    $item['class'],
                    $item['id'],
                    $item['parent'],
                    $item['url'] ?? null,
                    $item['alias'] ?? null,
                    $item['archive'] ?? false,
                    $item['roles'] ?? []
                );
            },
            $filtered
        );
    }

    /**
     * @return MfcMenuItemData[]
     */
    public function findAllPlain(): array
    {
        return $this->findAll([]);
    }
}
