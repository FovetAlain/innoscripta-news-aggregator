<?php

use App\Models\Article;
use App\Models\Source;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lists articles, 15 per page by default', function () {
    Article::factory()->count(20)->create();

    $this->getJson('/api/articles')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'title', 'provider', 'source', 'published_at']],
            'links',
            'meta',
        ])
        ->assertJsonCount(15, 'data'); // default page size
});

it('can search articles by keyword', function () {
    Article::factory()->create(['title' => 'Laravel rocks', 'description' => 'x']);
    Article::factory()->create(['title' => 'Unrelated', 'description' => 'y']);

    // search is case-insensitive (LIKE), so a lowercase query should still match
    $this->getJson('/api/articles?q=laravel')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('filters by source', function () {
    $source = Source::factory()->create();
    Article::factory()->for($source)->count(2)->create();
    Article::factory()->count(3)->create();

    $this->getJson('/api/articles?sources='.$source->id)
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('filters by published date (date_from)', function () {
    Article::factory()->create(['published_at' => '2026-01-01 00:00:00']);
    Article::factory()->create(['published_at' => '2026-06-01 00:00:00']);

    $this->getJson('/api/articles?date_from=2026-05-01')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('returns 422 when the date filter is garbage', function () {
    $this->getJson('/api/articles?date_from=not-a-date')->assertStatus(422);
});

it('shows a single article', function () {
    $article = Article::factory()->create();

    $this->getJson("/api/articles/{$article->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $article->id);
});

it('lists sources', function () {
    Source::factory()->count(3)->create();

    $this->getJson('/api/sources')->assertOk()->assertJsonCount(3, 'data');
});

it('feed only returns articles matching the user prefs', function () {
    $user = User::factory()->create();
    $source = Source::factory()->create();
    Article::factory()->for($source)->count(2)->create();
    Article::factory()->count(3)->create();
    $user->preference()->create(['preferred_sources' => [$source->id]]);
    Sanctum::actingAs($user);

    $this->getJson('/api/feed')->assertOk()->assertJsonCount(2, 'data');
});

it('feed is behind auth', function () {
    $this->getJson('/api/feed')->assertUnauthorized();
});
