<?php

use App\Enums\Provider;
use App\Services\News\Providers\GuardianProvider;
use App\Services\News\Providers\NewsApiProvider;
use App\Services\News\Providers\NytProvider;
use Illuminate\Support\Facades\Http;

it('maps a guardian payload into our ArticleData shape', function () {
    config()->set('news.providers.guardian.key', 'test-key');

    Http::fake(['content.guardianapis.com/*' => Http::response([
        'response' => ['results' => [[
            'id' => 'world/2026/jan/01/news',
            'webTitle' => 'Big news',
            'webUrl' => 'https://www.theguardian.com/world/2026/jan/01/news',
            'webPublicationDate' => '2026-01-01T10:00:00Z',
            'sectionName' => 'World news',
            'fields' => ['trailText' => 'desc', 'bodyText' => 'body', 'thumbnail' => 'img', 'byline' => 'Jane Doe'],
        ]]],
    ])]);

    $articles = collect((new GuardianProvider)->fetchLatest());

    expect($articles)->toHaveCount(1);
    expect($articles->first())
        ->provider->toBe(Provider::Guardian)
        ->externalId->toBe('world/2026/jan/01/news')
        ->sourceName->toBe('The Guardian')
        ->categoryName->toBe('World news')
        ->authorName->toBe('Jane Doe');
});

it('strips the "By " prefix off NYT bylines', function () {
    config()->set('news.providers.nyt.key', 'test-key');

    Http::fake(['api.nytimes.com/*' => Http::response([
        'results' => [[
            'title' => 'Headline',
            'url' => 'https://www.nytimes.com/2026/01/01/news.html',
            'published_date' => '2026-01-01T10:00:00Z',
            'section' => 'Technology',
            'abstract' => 'short',
            'byline' => 'By John Smith',
            'multimedia' => [['url' => 'https://img']],
        ]],
    ])]);

    $article = collect((new NytProvider)->fetchLatest())->first();

    expect($article->authorName)->toBe('John Smith')
        ->and($article->imageUrl)->toBe('https://img');
});

it('skips the fetch entirely when the api key is missing', function () {
    config()->set('news.providers.newsapi.key', null);
    Http::fake();

    expect(collect((new NewsApiProvider)->fetchLatest()))->toBeEmpty();
    Http::assertNothingSent();
});
