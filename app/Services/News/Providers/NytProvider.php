<?php

namespace App\Services\News\Providers;

use App\Enums\Provider;
use App\Services\News\Contracts\NewsProvider;
use App\Services\News\DTO\ArticleData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NytProvider implements NewsProvider
{
    public function provider(): Provider
    {
        return Provider::Nyt;
    }

    public function fetchLatest(): iterable
    {
        $config = config('news.providers.'.Provider::Nyt->value);

        if (blank($config['key'] ?? null)) {
            Log::warning('NYT API key missing, skipping fetch.');

            return [];
        }

        $response = Http::baseUrl($config['base_url'])
            ->timeout(15)
            ->retry(2, 200)
            ->get('/topstories/v2/home.json', ['api-key' => $config['key']]);

        if ($response->failed()) {
            Log::warning('NYT fetch failed', ['status' => $response->status()]);

            return [];
        }

        $articles = [];

        foreach ($response->json('results', []) as $item) {
            if (blank($item['url'] ?? null) || blank($item['title'] ?? null)) {
                continue;
            }

            $articles[] = $this->toArticle($item);
        }

        return $articles;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function toArticle(array $item): ArticleData
    {
        // nyt bylines come in like "By John Smith", we only want the name part.
        // note: we recieve the section as the category here, good enough for now
        $byline = $item['byline'] ?? null;

        return new ArticleData(
            provider: Provider::Nyt,
            externalId: $item['url'],
            title: $item['title'],
            url: $item['url'],
            publishedAt: CarbonImmutable::parse($item['published_date']),
            sourceName: 'The New York Times',
            description: $item['abstract'] ?? null,
            content: null,
            imageUrl: $item['multimedia'][0]['url'] ?? null,
            categoryName: $item['section'] ?? null,
            authorName: $byline ? Str::of($byline)->after('By ')->trim()->value() : null,
        );
    }
}
