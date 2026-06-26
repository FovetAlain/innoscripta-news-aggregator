<?php

namespace App\Services\News\DTO;

use App\Enums\Provider;
use Carbon\CarbonImmutable;

/**
 * Normalized article, shared shape every provider maps its payload into.
 */
final readonly class ArticleData
{
    public function __construct(
        public Provider $provider,
        public string $externalId,
        public string $title,
        public string $url,
        public CarbonImmutable $publishedAt,
        public string $sourceName,
        public ?string $description = null,
        public ?string $content = null,
        public ?string $imageUrl = null,
        public ?string $categoryName = null,
        public ?string $authorName = null,
    ) {}
}
