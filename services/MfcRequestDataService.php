<?php

declare(strict_types=1);

namespace common\modules\mfc\services;

use common\exceptions\UploadFileException;
use common\Helpers\BestSystemEverHelper;
use common\Helpers\FileHelper;
use common\Helpers\JsonHelper;
use common\modules\mfc\helpers\FormSchemeHelper;
use common\modules\mfc\MfcModule;
use common\modules\mfc\models\MfcEnquiryType;
use common\modules\upload\services\UploadSaveService;
use common\modules\upload\services\UploadService;
use DateTime;
use Exception;
use RuntimeException;
use Yii;
use yii\helpers\HtmlPurifier;
use yii\validators\FileValidator;
use yii\web\UploadedFile;
use ZipArchive;

/**
 * Сервис по обработке данных заявки "Единого окна"
 */
class MfcRequestDataService
{
    private const KEY_SKIP = 888;
    private const UPLOAD_DIR = 'mfc';
    private const FILE_MULTIPLE_MAX_COUNT = 10;
    private const FILE_MAX_SIZE_MB = 6;
    private const FILE_ALLOWED_EXTENSIONS = [
        'pdf',
        'doc',
        'docx',
        'rtf',
        'jpg',
        'jpeg',
        'png',
        'gif',
        'zip',
    ];
    private const FIELDS_SPECIAL = ['phone', 'comment'];

    private array $rawData;
    private array $uploadedFilesPaths = [];
    private array $filteredData;
    private FormSchemeHelper $formSchemeHelper;
    private UploadSaveService $uploadSaveService;
    private UploadService $uploadService;

    /**
     * @param MfcEnquiryType $mfcEnquiryType Тип заявки
     * @param array $rawData Данные, полученные от пользователя
     *
     * @throws Exception
     */
    public function __construct(MfcEnquiryType $mfcEnquiryType, array $rawData)
    {
        $this->rawData = $rawData;
        $this->uploadSaveService = new UploadSaveService();
        $this->uploadService = new UploadService();
        $this->formSchemeHelper = new FormSchemeHelper($mfcEnquiryType->getStructure());
        $this->filteredData = $this->getElements($mfcEnquiryType->getStructure());
    }

    /**
     * Отфильтрованные данные пользователя
     *
     * @return array
     */
    public function getFilteredData(): array
    {
        return $this->filteredData;
    }

    /**
     * Отфильтрованные данные пользователя для сохранения в БД
     *
     * @return string
     */
    public function getFilteredDataForSave(): string
    {
        $result = [];

        foreach ($this->filteredData as $dataItemKey => $dataItemValue) {
            if (!in_array($dataItemKey, self::FIELDS_SPECIAL, true)) {
                $result[$dataItemKey] = $dataItemValue;
            }
        }

        return JsonHelper::jsonEncode(
            $result,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Допустимые расширения загружаемых файлов
     *
     * @return array
     */
    public static function getAllowedFileExtensions(): array
    {
        return self::FILE_ALLOWED_EXTENSIONS;
    }

    /**
     * Максимальный размер файла в Мб
     *
     * @return int
     */
    public static function getMaxFileSizeMb(): int
    {
        return self::FILE_MAX_SIZE_MB;
    }

    /**
     * Удаление всех загруженных файлов
     */
    public function deleteUploadedFiles(): void
    {
        foreach ($this->uploadedFilesPaths as $uploadedFilePath) {
            if (file_exists($uploadedFilePath)) {
                FileHelper::unlink($uploadedFilePath);
            }
        }
    }

    /**
     * Элементы структуры
     *
     * @param array $structure Структура
     * @param string|null $parentName
     *
     * @return array
     * @throws Exception
     */
    private function getElements(array $structure, string $parentName = null): array
    {
        $result = [];

        try {
            foreach ($structure as $formElement) {
                $formElementName = $this->formSchemeHelper->getName($formElement);

                if (
                    !$formElementName
                    || $this->formSchemeHelper->isHeading($formElement)
                    || $this->formSchemeHelper->isGhost($formElement)
                ) {
                    continue;
                }

                if ($this->formSchemeHelper->isFieldset($formElement)) {
                    $_parentName = $parentName;

                    if (!$_parentName) {
                        $_parentName = $this->formSchemeHelper->isMultiple($formElement) ?
                            $formElementName :
                            null;
                    }

                    $elements = $this->getElements(
                        $this->formSchemeHelper->getChildren($formElement),
                        $_parentName
                    );

                    $result = $this->merge($result, $elements);

                    continue;
                }

                if ($this->formSchemeHelper->isFile($formElement)) {
                    if ($parentName) {
                        if (!isset($result[$parentName])) {
                            $result[$parentName] = [];
                        }

                        $files = UploadedFile::getInstancesByName(
                            sprintf('Enquiry[%s]', $formElementName)
                        );

                        $counter = 0;

                        foreach ($files as $file) {
                            if (!isset($result[$parentName][$counter])) {
                                $result[$parentName][$counter] = [];
                            }

                            $uploadedFile = $this->uploadFile($file, $formElement);

                            $result[$parentName][$counter][$formElementName] = $uploadedFile;
                            $counter++;
                        }
                    } elseif ($this->formSchemeHelper->isFileMultiple($formElement)) {
                        $files = UploadedFile::getInstancesByName(
                            sprintf('Enquiry[%s]', $formElementName)
                        );

                        if (!empty($files)) {
                            $result[$formElementName] = $this->uploadFileMultiple($files, $formElement);
                        }
                    } else {
                        $file = UploadedFile::getInstanceByName(
                            sprintf('Enquiry[%s]', $formElementName)
                        );

                        if ($file) {
                            $result[$formElementName] = $this->uploadFile($file, $formElement);
                        }
                    }

                    continue;
                }

                if ($this->formSchemeHelper->isField($formElement)) {
                    if (empty($this->rawData[$formElementName])) {
                        continue;
                    }

                    $formElementFormat = $this->formSchemeHelper->getFormat($formElement);

                    if (!$formElementFormat) {
                        continue;
                    }

                    $value = $this->rawData[$formElementName];

                    if ($parentName && is_array($value)) {
                        if (!isset($result[$parentName])) {
                            $result[$parentName] = [];
                        }

                        if ('multiselect' === $formElementFormat) {
                            foreach ($value as $valueItem) {
                                foreach ($valueItem as $k => $v) {
                                    if (!isset($result[$parentName][$k])) {
                                        $result[$parentName][$k] = [];
                                    }

                                    if (!isset($result[$parentName][$k][$formElementName])) {
                                        $result[$parentName][$k][$formElementName] = [];
                                    }

                                    $result[$parentName][$k][$formElementName][] = $this->getElementValue(
                                        'string',
                                        $v
                                    );
                                }
                            }
                        } else {
                            foreach ($value as $k => $v) {
                                if (self::KEY_SKIP === $k) {
                                    continue;
                                }

                                if (!isset($result[$parentName][$k])) {
                                    $result[$parentName][$k] = [];
                                }

                                $result[$parentName][$k][$formElementName] = $this->getElementValue(
                                    $formElementFormat,
                                    $v
                                );
                            }

                            /*if ('vehicleDriverFullName' === $formElement['name']) {
                                dd($result);
                            }*/
                        }
                    } else {
                        $result[$formElementName] = $this->getElementValue(
                            $formElementFormat,
                            $value
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $this->deleteUploadedFiles();

            throw $e;
        }

        return $result;
    }

    /**
     * Значение поля ввода
     *
     * @param string $format
     * @param $value
     *
     * @return mixed
     * @throws Exception
     */
    private function getElementValue(string $format, $value)
    {
        if (is_array($value)) {
            return $value;
        }

        switch ($format) {
            case 'date':
            case 'datetime':
                $time = strtotime($value);
                $datetime = (new DateTime())->setTimestamp($time);

                return BestSystemEverHelper::getDateIso($datetime);
            case 'integer':
                return (int)$value;
            case 'string':
                return HtmlPurifier::process((string)$value);
            default:
                return $value;
        }
    }

    /**
     * Загрузка группы файлов на сервер
     *
     * @param UploadedFile[] $files Группа файлов
     * @param array $formElement
     *
     * @return array
     * @throws UploadFileException
     */
    private function uploadFileMultiple(array $files, array $formElement): array
    {
        if (count($files) > self::FILE_MULTIPLE_MAX_COUNT) {
            throw new RuntimeException(
                MfcModule::t(
                    'common',
                    'ERROR_FILE_MULTIPLE_MAX_COUNT',
                    ['max_count' => self::FILE_MULTIPLE_MAX_COUNT]
                )
            );
        }

        $zip = new ZipArchive();

        $zipFileName = FileHelper::getFileNameWithExtension(
            FileHelper::getUniqueFileName(),
            'zip'
        );

        $zipFilePath = FileHelper::getPathFromArray([
            $this->uploadService->getAbsolutePath(),
            $zipFileName
        ]);

        $zipResultCode = $zip->open($zipFilePath, ZipArchive::CREATE);

        if ($zipResultCode === true) {
            $savedFilesPaths = [];

            foreach ($files as $file) {
                $uploadedFile = $this->uploadFile($file, $formElement);
                $fileAbsolutePath = $this->uploadService->getAbsolutePath(
                    $uploadedFile['path']
                );
                $fileName = FileHelper::getFileNameWithExtension(
                    $uploadedFile['base_name'],
                    $uploadedFile['type']
                );
                $zip->addFile($fileAbsolutePath, $fileName);
                $savedFilesPaths[] = $fileAbsolutePath;
            }

            $zip->close();

            foreach ($savedFilesPaths as $savedFilePath) {
                if (file_exists($savedFilePath)) {
                    unlink($savedFilePath);
                }
            }

            $uploadedFile = $this->uploadSaveService->putFromTempFile(
                $zipFilePath,
                self::UPLOAD_DIR
            );

            $this->uploadedFilesPaths[] = $uploadedFile['path'];

            return $uploadedFile;
        }

        throw new RuntimeException(
            MfcModule::t('common', 'ERROR_FILE_NOT_SAVED'),
            $zipResultCode
        );
    }

    /**
     * Загрузка файла на сервер
     *
     * @param UploadedFile $file Файл
     * @param array $formElement
     *
     * @return array
     * @throws UploadFileException
     */
    private function uploadFile(UploadedFile $file, array $formElement): array
    {
        $sizeFormatBase = Yii::$app->formatter->sizeFormatBase;
        Yii::$app->formatter->sizeFormatBase = 1000;

        $fileExtensions = $this->formSchemeHelper->getFileExtensions($formElement);

        $fileValidator = new FileValidator([
            'extensions' => $fileExtensions,
            'maxSize' => $this->getFileMaxSizeInBytes(),
        ]);

        $error = null;
        $fileValidator->validate($file, $error);

        Yii::$app->formatter->sizeFormatBase = $sizeFormatBase;

        if ($error) {
            throw new RuntimeException($error);
        }

        $uploadedFile = $this->uploadSaveService->saveUploadedFile($file, self::UPLOAD_DIR);
        $this->uploadedFilesPaths[] = $uploadedFile['path'];

        return $uploadedFile;
    }

    /**
     * Максимальный размер загружаемого файла в байтах
     *
     * @return int
     */
    private function getFileMaxSizeInBytes(): int
    {
        return self::FILE_MAX_SIZE_MB * 1000 * 1000;
    }

    /**
     * Хитрое объединение двух массивов
     *
     * @param array $a Массив 1
     * @param array $b Массив 2
     *
     * @return array
     */
    private function merge(array $a, array $b): array
    {
        $result = [];
        $_b = $b;

        foreach ($a as $k => $v) {
            $_k = $k;
            $_v = $v;

            if (isset($_b[$_k]) && is_array($_b[$_k])) {
                $_v = $this->merge($_v, $_b[$_k]);
                unset($_b[$_k]);
            }

            $result[$_k] = $_v;
        }

        return array_merge($result, $_b);
    }
}
