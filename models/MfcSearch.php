<?php

declare(strict_types=1);

namespace common\modules\mfc\models;

class MfcSearch
{
    public string $title;
    public string $description;
    public string $url;
    public string $type;
    public array $roles;
    public string $keywords;

    public function __construct(
        string $title,
        string $description,
        string $url,
        string $type,
        array $roles,
        string $keywords
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        $this->type = $type;
        $this->roles = $roles;
        $this->keywords = $keywords;
    }
}
