<?php

declare(strict_types=1);

namespace common\modules\mfc\services;

use common\modules\mfc\factories\CategoriesSchemeHelperFactory;
use common\modules\mfc\factories\MfcClassifierFactory;
use common\modules\mfc\helpers\ReadSchemeFileHelper;
use common\modules\mfc\models\MfcCategory;
use common\modules\mfc\models\MfcLink;
use common\modules\mfc\models\MfcSearch;

/**
 * Сервис парсинга данных из "Единого окна" для поиска
 */
class MfcParserServices
{
    private const SEARCH_TYPE = 'MFC';

    private MfcClassifierFactory $mfcClassifierFactory;
    private CategoriesSchemeHelperFactory $categoriesSchemeHelperFactory;

    public function __construct()
    {
        $this->mfcClassifierFactory = new MfcClassifierFactory();
        $this->categoriesSchemeHelperFactory = new CategoriesSchemeHelperFactory(new ReadSchemeFileHelper());
    }

    /**
     * Парсинг данных с последующей обработкой
     *
     * @return array
     */
    public function parsingMfcForSearch(): array
    {
        return $this->prepareItems($this->getItemsFromMfc());
    }

    /**
     * Получаем данные из MFC
     *
     * @return array
     */
    private function getItemsFromMfc(): array
    {
        return $this->categoriesSchemeHelperFactory
            ->createObject(null, false)
            ->findAllPlain();
    }

    /**
     * Приводим данные в необходимый вид
     *
     * @param array $items
     *
     * @return MfcSearch[]
     */
    public function prepareItems(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $prepareItem = $this->mfcClassifierFactory->createMfcElement($item);

            if ($prepareItem instanceof MfcCategory || $prepareItem instanceof MfcLink) {
                continue;
            }

            $result[] = [
                'title' => $prepareItem->getTitle(),
                'description' => sprintf(
                    '%s' . PHP_EOL . '%s',
                    strip_tags($prepareItem->getHint()),
                    strip_tags($prepareItem->getDescription())
                ),
                'url' => $prepareItem->getUrlForSearchMfc(),
                'type' => self::SEARCH_TYPE,
                'roles' => $prepareItem->getRoles(),
                'keywords' => $prepareItem->getKeywords()
            ];
        }

        return array_map(
            static function (array $item) {
                return new MfcSearch(
                    $item['title'],
                    $item['description'],
                    $item['url'],
                    $item['type'],
                    $item['roles'],
                    $item['keywords']
                );
            },
            $result
        );
    }
}
