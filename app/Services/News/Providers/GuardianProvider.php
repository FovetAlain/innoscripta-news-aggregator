<?php

namespace App\Services\News\Providers;

use App\Enums\Provider;
use App\Services\News\Contracts\NewsProvider;
use App\Services\News\DTO\ArticleData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianProvider implements NewsProvider
{
    public function provider(): Provider
    {
        return Provider::Guardian;
    }

    public function fetchLatest(): iterable
    {
        $config = config('news.providers.'.Provider::Guardian->value);

        if (blank($config['key'] ?? null)) {
            Log::warning('Guardian API key missing, skipping fetch.');

            return [];
        }

        $response = Http::baseUrl($config['base_url'])
            ->timeout(15)
            ->retry(2, 200)
            ->get('/search', [
                'api-key' => $config['key'],
                'page-size' => config('news.page_size'),
                'order-by' => 'newest',
                'show-fields' => 'trailText,bodyText,thumbnail,byline',
            ]);

        if ($response->failed()) {
            Log::warning('Guardian fetch failed', ['status' => $response->status()]);

            return [];
        }

        return array_map(
            $this->toArticle(...),
            $response->json('response.results', []),
        );
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function toArticle(array $item): ArticleData
    {
        $fields = $item['fields'] ?? [];

        return new ArticleData(
            provider: Provider::Guardian,
            externalId: $item['id'],
            title: $item['webTitle'],
            url: $item['webUrl'],
            publishedAt: CarbonImmutable::parse($item['webPublicationDate']),
            sourceName: 'The Guardian',
            description: $fields['trailText'] ?? null,
            content: $fields['bodyText'] ?? null,
            imageUrl: $fields['thumbnail'] ?? null,
            categoryName: $item['sectionName'] ?? null,
            authorName: $fields['byline'] ?? null,
        );
    }
}
