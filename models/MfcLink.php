<?php

declare(strict_types=1);

namespace common\modules\mfc\models;

use common\modules\mfc\factories\MfcElementsRepositoryFactory;
use common\modules\mfc\helpers\FilterRolesHelper;
use common\modules\mfc\helpers\IdTranslationHelper;
use common\modules\mfc\interfaces\MenuItemInterface;
use common\modules\mfc\MfcModule;

/**
 * Внешняя ссылка
 */
class MfcLink implements MenuItemInterface
{
    private const LINK_PREFIX = 'LINK';
    private const LINK_SUFFIX = 'TITLE';
    private const HINT_SUFFIX = 'HINT';
    private const ICON_SUFFIX = 'ICON';

    private IdTranslationHelper $idTranslationHelper;
    private MfcElementsRepositoryFactory $mfcElementsRepositoryFactory;
    private FilterRolesHelper $filterRolesHelper;
    private string $id;
    private string $url;
    private ?array $roles;

    /**
     * @param string $id ID
     * @param string $url Адрес ссылки
     * @param array|null $roles Роли пользователей
     */
    public function __construct(string $id, string $url, ?array $roles = [])
    {
        $this->id = $id;
        $this->url = $url;
        $this->roles = $roles;
        $this->idTranslationHelper = new IdTranslationHelper();
        $this->filterRolesHelper = new FilterRolesHelper();
        $this->mfcElementsRepositoryFactory = new MfcElementsRepositoryFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return MfcModule::t(
            'common',
            $this->idTranslationHelper->getTranslationId(
                self::LINK_PREFIX,
                $this->id,
                self::LINK_SUFFIX
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getHint(): string
    {
        return MfcModule::t(
            'common',
            $this->idTranslationHelper->getTranslationId(
                self::LINK_PREFIX,
                $this->id,
                self::HINT_SUFFIX
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        $icon = MfcModule::t(
            'common',
            $this->idTranslationHelper->getTranslationId(
                self::LINK_PREFIX,
                $this->id,
                self::ICON_SUFFIX
            )
        );

        return ctype_upper(substr($icon, 0, 3)) ? '' : $icon;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Получить подкатегории на конкретного пользователя.
     *
     * @param int $userId
     *
     * @return array
     */
    public function getSubcategories(int $userId): array
    {
        $mfcElementsRepository = $this->mfcElementsRepositoryFactory->createRepository($userId, false);
        $children = $mfcElementsRepository->findAllForCategory($this->id);

        return $this->filterRolesHelper->filteredSubcategories($children);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlForSearchMfc(): string
    {
        return $this->getUrl();
    }
}
