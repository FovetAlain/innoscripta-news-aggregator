<?php

use App\Models\Article;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('news.providers.newsapi.key', 'k');
    config()->set('news.providers.guardian.key', 'k');
    config()->set('news.providers.nyt.key', 'k');

    Http::fake([
        'newsapi.org/*' => Http::response(['articles' => [[
            'source' => ['name' => 'BBC News'],
            'author' => 'Jane',
            'title' => 'NewsAPI headline',
            'description' => 'desc',
            'url' => 'https://news.example/a',
            'urlToImage' => 'https://img/a',
            'publishedAt' => '2026-01-01T08:00:00Z',
            'content' => 'body',
        ]]]),
        'content.guardianapis.com/*' => Http::response(['response' => ['results' => [[
            'id' => 'world/2026/news',
            'webTitle' => 'Guardian headline',
            'webUrl' => 'https://gu.example/a',
            'webPublicationDate' => '2026-01-01T09:00:00Z',
            'sectionName' => 'World',
            'fields' => ['trailText' => 'desc', 'byline' => 'John'],
        ]]]]),
        'api.nytimes.com/*' => Http::response(['results' => [[
            'title' => 'NYT headline',
            'url' => 'https://nyt.example/a',
            'published_date' => '2026-01-01T10:00:00Z',
            'section' => 'Technology',
            'abstract' => 'desc',
            'byline' => 'By Sam',
            'multimedia' => [['url' => 'https://img/nyt']],
        ]]]),
    ]);
});

it('imports articles from every provider', function () {
    $this->artisan('news:fetch', ['--sync' => true])->assertSuccessful();

    expect(Article::count())->toBe(3);
});

it('doesnt create duplicates when run twice', function () {
    $this->artisan('news:fetch', ['--sync' => true])->assertSuccessful();
    $this->artisan('news:fetch', ['--sync' => true])->assertSuccessful();

    // same upstream payload -> second run should updateOrCreate, not insert again
    expect(Article::count())->toBe(3);
});
