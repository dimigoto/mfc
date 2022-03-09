<?php

declare(strict_types=1);

namespace common\modules\mfc\models;

use common\exceptions\NotFoundException;
use common\Helpers\TextHelper;
use common\modules\mfc\factories\MfcElementsRepositoryFactory;
use common\modules\mfc\helpers\FilterRolesHelper;
use common\modules\mfc\helpers\IdTranslationHelper;
use common\modules\mfc\helpers\ReadSchemeFileHelper;
use common\modules\mfc\interfaces\MenuItemInterface;
use common\modules\mfc\MfcModule;
use common\modules\mfc\repositories\MfcRequestRepository;
use common\modules\userRequest\models\UserRequest;
use common\modules\userUniver\models\User;
use http\Exception\RuntimeException;
use Yii;
use yii\helpers\Url;

/**
 * Тип заявки в "Единое окно"
 *
 * @property-read string $schemeName
 * @property-read string $url
 */
class MfcEnquiryType implements MenuItemInterface
{
    private const ENQUIRY_PREFIX = 'ENQUIRY';
    private const TITLE_SUFFIX = 'TITLE';
    private const HINT_SUFFIX = 'HINT';
    private const DESCRIPTION_SUFFIX = 'DESCRIPTION';
    private const ICON_SUFFIX = 'ICON';

    private const REFERENCE_BODY = 'REFERENCE_LETTER_TYPE';
    private const CHARACTERISTIC_BODY = 'STUDENT_CHARACTERISTIC_TYPE';
    private const DOCUMENT_BODY = 'EMPLOYEE_DOCUMENT_COPY_CATEGORY';
    private const SECONDS_IN_MINUTE = 60;
    private const SECONDS_IN_HOUR = 60 * self::SECONDS_IN_MINUTE;
    private const DEFAULT_RATE_LIMIT = self::SECONDS_IN_HOUR;

    private string $id;
    private ?string $alias;
    private bool $isArchive;
    private ?array $roles;

    private ?array $scheme = null;
    private $rateLimit = self::DEFAULT_RATE_LIMIT;
    private ?UserRequest $recentRequest = null;

    private ReadSchemeFileHelper $readSchemeFileHelper;
    private IdTranslationHelper $idTranslationHelper;
    private FilterRolesHelper $filterRolesHelper;
    private MfcRequestRepository $mfcRequestRepository;
    private MfcElementsRepositoryFactory $mfcElementsRepositoryFactory;

    /**
     * @param string $id ID заявки
     * @param string|null $alias Псевдоним заявки
     * @param bool $isArchive Архивная заявка
     * @param array|null $roles Роли пользователей
     */
    public function __construct(string $id, ?string $alias, bool $isArchive = false, ?array $roles = [])
    {
        $this->id = $id;
        $this->alias = $alias;
        $this->isArchive = $isArchive;
        $this->roles = $roles;

        $this->readSchemeFileHelper = new ReadSchemeFileHelper();
        $this->idTranslationHelper = new IdTranslationHelper();
        $this->mfcRequestRepository = new MfcRequestRepository();
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
                self::ENQUIRY_PREFIX,
                $this->id,
                self::TITLE_SUFFIX
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
                self::ENQUIRY_PREFIX,
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
        return Url::toRoute(['default/enquiry', 'id' => $this->id]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlForSearchMfc(): string
    {
        return sprintf(
            '/mfc/default/enquiry?id=%s',
            $this->id
        );
    }

    /**
     * Получить описание услуги.
     *
     * @return string
     */
    public function getDescription(): string
    {
        $messKey = $this->idTranslationHelper->getTranslationId(
            self::ENQUIRY_PREFIX,
            $this->getId(),
            self::DESCRIPTION_SUFFIX
        );
        $mess = MfcModule::t(
            'common',
            $messKey
        );
        if ($mess === $messKey && !empty($this->alias)) {
            $mess = MfcModule::t(
                'common',
                $this->idTranslationHelper->getTranslationId(
                    self::ENQUIRY_PREFIX,
                    $this->alias,
                    self::DESCRIPTION_SUFFIX
                )
            );
        }
        return $mess;
    }

    /**
     * Получаем ключевые слова, из видов справок, типов документов, характеристик итд
     *
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->parseKeywords($this->getStructure());
    }

    /**
     * Парсим информацию для ключевых слов.
     *
     * @param array $structure
     *
     * @return string
     */
    public function parseKeywords(array $structure): string
    {
        $result = [];

        foreach ($structure as $item) {
            if (isset($item['label']) && $item['label'] === self::REFERENCE_BODY) {
                $result[] = $this->readSchemeFileHelper->getReferences($item['items']);
            }

            if (isset($item['label']) && $item['label'] === self::DOCUMENT_BODY) {
                $result[] = $this->readSchemeFileHelper->getDocuments($item['items']);
            }

            if (isset($item['label']) && $item['label'] === self::CHARACTERISTIC_BODY) {
                $result[] = $this->readSchemeFileHelper->getCharacteristics($item['items']);
            }
        }

        return implode(PHP_EOL, $result);
    }

    /**
     * Признак архивной заявки
     */
    public function isArchive(): bool
    {
        return $this->isArchive;
    }

    /**
     * В заявке есть файл с бланком для скачивания
     *
     * @return bool
     */
    public function hasFileToDownload(): bool
    {
        return !empty($this->getFormsFiles());
    }

    /**
     * Файлы бланков
     *
     * @return array|null
     */
    public function getFormsFiles(): ?array
    {
        $scheme = $this->getScheme();

        if (empty($scheme['files'])) {
            return null;
        }

        return $scheme['files'];
    }

    /**
     * Путь к файлу с бланком заявления
     *
     * @param string $guid GUID файла бланка
     *
     * @return string|null
     */
    public function getFilePath(string $guid): ?string
    {
        $fileName = $this->getFilenameById($guid);

        if (empty($fileName)) {
            return null;
        }

        $path = sprintf(
            '%s/modules/mfc/files/%s',
            Yii::getAlias('@common'),
            $fileName
        );

        if (!file_exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * GUID типа заявки
     *
     * @return string
     */
    public function getGuidEnquiryType(): string
    {
        if (empty($this->scheme['guidRequestType'])) {
            throw new RuntimeException('GUID типа заявки не определён');
        }

        return $this->scheme['guidRequestType'];
    }

    /**
     * Структура формы
     *
     * @return array
     */
    public function getStructure(): array
    {
        $scheme = $this->getScheme();

        if (empty($scheme['structure'])) {
            throw new RuntimeException('Структура формы не определена');
        }

        return $scheme['structure'];
    }

    /**
     * Проверка, может ли пользователь прямо сейчас отправить заявку данного типа
     *
     * @param User $user Пользователь
     *
     * @return bool
     */
    public function canSaveRequestByRateLimit(User $user): bool
    {
        return 0 === $this->getRateLimit()
            || 0 === $this->userMustWaitInSeconds($user);
    }

    /**
     * Время до следующей возможности отправить заявку данного типа для вывода пользователю
     *
     * @param User $user Пользователь
     *
     * @return string
     */
    public function getTimeToWaitDisplay(User $user): string
    {
        $result = [];

        $secondsToWait = $this->userMustWaitInSeconds($user);
        $hours = (int)floor(
            $secondsToWait / self::SECONDS_IN_HOUR
        );

        if ($hours > 0) {
            $result[] = sprintf(
                '%s %s',
                $hours,
                TextHelper::numDeclension($hours, ['час', 'часа', 'часов'])
            );
        }

        $minutes = (int)ceil(
            ($secondsToWait - $hours * self::SECONDS_IN_HOUR) / self::SECONDS_IN_MINUTE
        );

        if ($minutes > 0) {
            $result[] = sprintf(
                '%s %s',
                $minutes,
                TextHelper::numDeclension($minutes, ['минута', 'минуты', 'минут'])
            );
        }

        return implode(' ', $result);
    }

    /**
     * Количество секунд до следующей возможности отправить заявку данного типа
     *
     * @param User $user Пользователь
     *
     * @return int
     */
    private function userMustWaitInSeconds(User $user): int
    {
        try {
            if (!$this->recentRequest) {
                $this->recentRequest = $this->findRecentRequest($user);
            }

            $nextTime = $this->recentRequest->created_at + $this->getRateLimit();
            $timeToWait = $nextTime - time();

            return ($timeToWait > 0) ? $timeToWait : 0;
        } catch (NotFoundException $e) {
            //Если таких заявок ещё не было, пропускаем
            return 0;
        }
    }

    /**
     * Крайняя для пользователя заявка заданного типа
     *
     * @param User $user Пользователь
     *
     * @return UserRequest
     * @throws NotFoundException
     */
    private function findRecentRequest(User $user): UserRequest
    {
        return $this->mfcRequestRepository->findOneRecentForUserBySubtype($user, $this->id);
    }

    /**
     * Ограничение на интервал времени между заявками одного типа
     *
     * @return int
     */
    private function getRateLimit(): int
    {
        $scheme = $this->getScheme();

        if (isset($scheme['rateLimit'])) {
            $this->rateLimit = (int)$scheme['rateLimit'];
        }

        return $this->rateLimit;
    }

    /**
     * Схема
     *
     * @return array
     */
    private function getScheme(): array
    {
        if (!$this->scheme) {
            $this->scheme = $this->readSchemeFileHelper->readScheme(
                $this->getRealId()
            );
            $this->scheme['structure'] = $this->parseStructure($this->scheme['structure']);
        }

        return $this->scheme;
    }

    /**
     * @return string
     */
    private function getRealId(): string
    {
        return $this->alias ?? $this->id;
    }

    /**
     * Рекурсивная сборка структуры из частей
     *
     * @param array $structure Структура
     *
     * @return array
     */
    private function parseStructure(array $structure): array
    {
        $result = [];

        foreach ($structure as $key => $val) {
            if ('include' === $val['class']) {
                $subScheme = $this->readSchemeFileHelper->readScheme($val['src']);

                if ($this->hasStringKeys($subScheme)) {
                    $result[] = $subScheme;
                } else {
                    $subSchemeParsed = $this->parseStructure($subScheme);

                    foreach ($subSchemeParsed as $subSchemeItem) {
                        $result[] = $subSchemeItem;
                    }
                }
            } else {
                $result[] = $val;
            }
        }

        return $result;
    }

    /**
     * Проверка, является ли массив ассоциативным
     *
     * @param array $array Массив
     *
     * @return bool
     */
    private function hasStringKeys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Имя файла бланка по GUID-у
     *
     * @param string $guid GUID файла бланка
     *
     * @return mixed|null
     */
    private function getFilenameById(string $guid): ?string
    {
        $formsFiles = $this->getFormsFiles();

        if (empty($formsFiles)) {
            return null;
        }

        foreach ($formsFiles as $formsFile) {
            if (isset($formsFile['guid']) && $formsFile['guid'] === $guid) {
                return $formsFile['file'];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        $icon = MfcModule::t(
            'common',
            $this->idTranslationHelper->getTranslationId(
                self::ENQUIRY_PREFIX,
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
     * Получить подкатегории на конкретного пользователя
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
}
