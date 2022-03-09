<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers;

use common\Helpers\JsonHelper;
use common\modules\mfc\interfaces\ReaderSchemeInterface;
use common\modules\mfc\MfcModule;
use SplFileObject;
use Yii;

/**
 * Вспомогательные функции для чтения схем из файлов
 */
class ReadSchemeFileHelper implements ReaderSchemeInterface
{
    private const FILE_EXTENSION = 'json';
    private const REFERENCE_BODY_ITEMS = 'REFERENCE_LETTER_TYPE_ITEMS';
    private const CHARACTERISTIC_BODY_ITEMS = 'STUDENT_CHARACTERISTIC_TYPE_ITEMS';
    private const DOCUMENT_BODY_ITEMS = 'EMPLOYEE_DOCUMENT_COPY_CATEGORY_ITEMS';

    /**
     * {@inheritdoc}
     */
    public function readScheme(string $name): array
    {
        $file = new SplFileObject(
            sprintf(
                '%s/modules/mfc/schemes/%s.%s',
                Yii::getAlias('@common'),
                $name,
                self::FILE_EXTENSION
            ),
            'r'
        );

        $scheme = $file->fread($file->getSize());

        return JsonHelper::jsonDecode($scheme);
    }

    /**
     * Список справок
     *
     * @param array $references
     *
     * @return string
     */
    public function getReferences(array $references): string
    {
        $result = array_map(
            static function (string $item) {
                return
                    MfcModule::t(
                        'common',
                        sprintf(self::REFERENCE_BODY_ITEMS . '_' . '%s', $item)
                    );
            },
            $references
        );

        return implode(PHP_EOL, array_slice($result, 1));
    }

    /**
     * Список документов
     *
     * @param array $documents
     *
     * @return string
     */
    public function getDocuments(array $documents): string
    {
        $result = array_map(
            static function (string $item) {
                return
                    MfcModule::t(
                        'common',
                        sprintf(self::DOCUMENT_BODY_ITEMS . '_' . '%s', $item)
                    );
            },
            $documents
        );

        return implode(PHP_EOL, $result);
    }

    /**
     * Список характеристик
     *
     * @param array $characteristics
     *
     * @return string
     */
    public function getCharacteristics(array $characteristics): string
    {
        $result = array_map(
            static function (string $item) {
                return
                    MfcModule::t(
                        'common',
                        sprintf(self::CHARACTERISTIC_BODY_ITEMS . '_' . '%s', $item)
                    );
            },
            $characteristics
        );

        return implode(PHP_EOL, $result);
    }
}
