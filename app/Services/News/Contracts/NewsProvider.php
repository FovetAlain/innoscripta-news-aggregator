<?php

namespace App\Services\News\Contracts;

use App\Enums\Provider;
use App\Services\News\DTO\ArticleData;

interface NewsProvider
{
    public function provider(): Provider;

    /**
     * Pull the latest articles from the upstream API.
     *
     * @return iterable<ArticleData>
     */
    public function fetchLatest(): iterable;
}
