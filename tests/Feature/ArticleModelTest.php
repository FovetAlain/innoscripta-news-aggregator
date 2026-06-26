<?php

use App\Enums\Provider;
use App\Models\Article;
use App\Models\Source;
use Carbon\CarbonInterface;

it('casts provider and published_at', function () {
    $article = Article::factory()->create(['provider' => Provider::Guardian]);

    expect($article->provider)->toBe(Provider::Guardian)
        ->and($article->published_at)->toBeInstanceOf(CarbonInterface::class);
});

it('search scope looks at both title and description', function () {
    Article::factory()->create(['title' => 'Laravel ships a new release']);
    // second one only mentions laravel in the description, still expect a hit
    Article::factory()->create(['title' => 'Something unrelated', 'description' => 'about laravel internals']);
    Article::factory()->create(['title' => 'Cooking pasta', 'description' => 'tomatoes']);

    expect(Article::search('laravel')->count())->toBe(2);
});

it('filters by source', function () {
    $source = Source::factory()->create();
    Article::factory()->for($source)->count(2)->create();
    Article::factory()->count(3)->create();

    expect(Article::forSources([$source->id])->count())->toBe(2);
});
