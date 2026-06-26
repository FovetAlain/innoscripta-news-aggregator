<?php

namespace App\Services\News\Providers;

use App\Enums\Provider;
use App\Services\News\Contracts\NewsProvider;
use App\Services\News\DTO\ArticleData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiProvider implements NewsProvider
{
    public function provider(): Provider
    {
        return Provider::NewsApi;
    }

    public function fetchLatest(): iterable
    {
        $config = config('news.providers.'.Provider::NewsApi->value);

        if (blank($config['key'] ?? null)) {
            Log::warning('NewsAPI key missing, skipping fetch.');

            return [];
        }

        $articles = [];

        // top-headlines endpoint doesn't give us a category back, so we loop the
        // configured categories and tag each batch ourselves
        foreach ($config['categories'] as $category) {
            $response = Http::baseUrl($config['base_url'])
                ->timeout(15)
                ->retry(2, 200)
                ->get('/top-headlines', [
                    'apiKey' => $config['key'],
                    'language' => 'en',
                    'category' => $category,
                    'pageSize' => config('news.page_size'),
                ]);

            if ($response->failed()) {
                Log::warning('NewsAPI fetch failed', ['category' => $category, 'status' => $response->status()]);

                continue;
            }

            // dump($response->json('articles'));

            foreach ($response->json('articles', []) as $item) {
                // sometimes newsapi returns articles with a null url/title, skip those
                if (blank($item['url'] ?? null) || blank($item['title'] ?? null)) {
                    continue;
                }

                $articles[] = $this->toArticle($item, $category);
            }
        }

        return $articles;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function toArticle(array $item, string $category): ArticleData
    {
        return new ArticleData(
            provider: Provider::NewsApi,
            externalId: $item['url'], // no stable id from newsapi, url is the best we have
            title: $item['title'],
            url: $item['url'],
            publishedAt: CarbonImmutable::parse($item['publishedAt']),
            sourceName: $item['source']['name'] ?? 'Unknown',
            description: $item['description'] ?? null,
            content: $item['content'] ?? null,
            imageUrl: $item['urlToImage'] ?? null,
            categoryName: ucfirst($category),
            authorName: $item['author'] ?? null,
        );
    }
}
