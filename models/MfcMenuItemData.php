<?php

declare(strict_types=1);

namespace common\modules\mfc\models;

/**
 * Элемент навигации по иерархии заявок
 */
class MfcMenuItemData
{
    public const CLASS_CATEGORY = 'category';
    public const CLASS_ENQUIRY = 'enquiry';
    public const CLASS_LINK = 'link';

    private string $id;
    private ?string $parentId;
    private string $class;
    private ?string $url;
    private ?string $alias;
    private bool $isArchive;
    private ?array $roles;

    public function __construct(
        string $class,
        string $id,
        ?string $parentId,
        ?string $url,
        ?string $alias,
        bool $isArchive,
        ?array $roles = []
    ) {
        $this->class = $class;
        $this->id = $id;
        $this->parentId = $parentId;
        $this->url = $url;
        $this->alias = $alias;
        $this->isArchive = $isArchive;
        $this->roles = $roles;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function isArchive(): bool
    {
        return $this->isArchive;
    }

    /**
     * @return bool
     */
    public function hasAlias(): bool
    {
        return !empty($this->alias);
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function isCategory(): bool
    {
        return $this->class === self::CLASS_CATEGORY;
    }

    /**
     * @return bool
     */
    public function isEnquiry(): bool
    {
        return $this->class === self::CLASS_ENQUIRY;
    }

    /**
     * @return bool
     */
    public function isLink(): bool
    {
        return $this->class === self::CLASS_LINK;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
