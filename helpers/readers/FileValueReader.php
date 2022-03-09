<?php

declare(strict_types=1);

namespace common\modules\mfc\helpers\readers;

use common\modules\mfc\interfaces\ReaderInterface;
use Yii;
use yii\bootstrap4\Html;
use yii\helpers\Url;

/**
 * Чтение файла
 */
class FileValueReader implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function readValue($value, array $formElement, array $options): string
    {
        if (!empty($options['number'])) {
            $text = sprintf('Скачать документ (%s)', $options['number']);
        } else {
            $text = 'Скачать документ';
        }

        return Html::a(
            //HtmlPurifier::process($value['base_name']),
            $text,
            Url::toRoute([
                '/mfc/default/file',
                'requestId' => $options['enquiryId'],
                'fileId' => $value['hash'],
            ]),
            [
                'target' => '_blank',
            ]
        ) . sprintf(', %s, %s', strtoupper($value['type']), $this->getSize($value['size']));
    }

    /**
     * Размер файла для вывода
     *
     * @param int $fileSizeInBytes Размер файла в байтах
     *
     * @return string
     */
    private function getSize(int $fileSizeInBytes): string
    {
        $sizeFormatBase = Yii::$app->formatter->sizeFormatBase;
        Yii::$app->formatter->sizeFormatBase = 1000;

        $result = Yii::$app->formatter->asShortSize(
            $fileSizeInBytes,
            ($fileSizeInBytes >= 1048576) ? 1 : 0
        );

        Yii::$app->formatter->sizeFormatBase = $sizeFormatBase;

        return $result;
    }
}
