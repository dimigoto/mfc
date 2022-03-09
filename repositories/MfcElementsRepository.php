<?php

declare(strict_types=1);

namespace common\modules\mfc\repositories;

use common\modules\mfc\factories\MfcClassifierFactory;
use common\modules\mfc\helpers\CategoriesSchemeHelper;
use common\modules\mfc\interfaces\MenuItemInterface;
use common\modules\mfc\models\MfcCategory;
use common\modules\mfc\models\MfcEnquiryType;
use common\modules\mfc\models\MfcMenuItemData;

/**
 * Коллекция элементов иерархии заявок
 */
class MfcElementsRepository
{
    private MfcClassifierFactory $classifierFactory;
    private CategoriesSchemeHelper $categoriesSchemeHelper;

    public function __construct(
        MfcClassifierFactory $classifierFactory,
        CategoriesSchemeHelper $categoriesSchemeHelper
    ) {
        $this->classifierFactory = $classifierFactory;
        $this->categoriesSchemeHelper = $categoriesSchemeHelper;
    }

    /**
     * Поиск всех элементов иерархии заявок для заданной категории
     *
     * @param string|null $parentId
     *
     * @return MenuItemInterface[]
     */
    public function findAllForCategory(string $parentId = null): array
    {
        $result = [];

        $children = $this->categoriesSchemeHelper->findAllChildren($parentId);

        foreach ($children as $child) {
            $result[] = $this->classifierFactory->createMfcElement($child);
        }

        return $result;
    }

    /**
     * Поиск всей иерархии заявок и категорий
     *
     * @return MenuItemInterface[]
     */
    public function findTreeOfCategoriesAndElements(string $parentId = null): array
    {
        $result = [];

        $children = $this->categoriesSchemeHelper->findAllChildren($parentId);

        /** @var MfcMenuItemData $child */
        foreach ($children as $child) {
            $item = [
                'item' => $this->classifierFactory->createMfcElement($child)
            ];
            if ($child->getClass() === MfcMenuItemData::CLASS_CATEGORY){
                $item['children'] = $this->findTreeOfCategoriesAndElements($child->getId());
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Поиск категории по ID
     *
     * @param string $id ID категории
     *
     * @return MfcCategory|null
     */
    public function findOneCategoryById(string $id): ?MfcCategory
    {
        $data = $this->categoriesSchemeHelper->findOneCategoryById($id);

        if ($data) {
            return $this->classifierFactory->createMfcCategory($id);
        }

        return null;
    }

    /**
     * @param string $enquiryId
     *
     * @return MfcCategory[]
     */
    public function findParentCategoriesByEnquiryTypeId(string $enquiryId): array
    {
        $result = [];

        $data = $this->categoriesSchemeHelper->findOneEnquiryById($enquiryId);

        if ($data) {
            $result = $this->findParents($data);
        }

        return $result;
    }

    /**
     * @param string $categoryId
     *
     * @return MfcCategory[]
     */
    public function findParentCategoriesByCategoryId(string $categoryId): array
    {
        $result = [];

        $data = $this->categoriesSchemeHelper->findOneCategoryById($categoryId);

        if ($data) {
            $result = $this->findParents($data);
        }

        return $result;
    }

    /**
     * @param string $enquiryId
     *
     * @return MfcCategory|null
     */
    public function findParentCategoryByEnquiryTypeId(string $enquiryId): ?MfcCategory
    {
        $data = $this->categoriesSchemeHelper->findOneEnquiryById($enquiryId);

        if ($data) {
            return $this->classifierFactory->createMfcCategory(
                $data->getParentId()
            );
        }

        return null;
    }

    /**
     * Поиск категории по ID
     *
     * @param string $id ID категории
     *
     * @return MfcEnquiryType|null
     */
    public function findOneEnquiryById(string $id): ?MfcEnquiryType
    {
        $data = $this->categoriesSchemeHelper->findOneEnquiryById($id);

        if ($data) {
            return $this->classifierFactory->createMfcEnquiryType($data->getId(), $data->getAlias());
        }

        return null;
    }

    /**
     * @param MfcMenuItemData $data
     *
     * @return array
     */
    private function findParents(MfcMenuItemData $data): array
    {
        $result = [];

        do {
            $parentId = $data->getParentId();

            if ($parentId) {
                $result[] = $this->classifierFactory->createMfcCategory($parentId);
                $data = $this->categoriesSchemeHelper->findOneCategoryById($parentId);
            }
        } while ($parentId);

        return array_reverse($result);
    }
}
