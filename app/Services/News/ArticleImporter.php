<?php

namespace App\Services\News;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Services\News\DTO\ArticleData;
use Illuminate\Support\Str;

class ArticleImporter
{
    // small in-memory cache so we don't query the same source/category/author
    // over and over for one import run. keyed by slug.
    /** @var array<string, int> */
    private array $sources = [];

    /** @var array<string, int> */
    private array $categories = [];

    /** @var array<string, int> */
    private array $authors = [];

    /**
     * Upsert a batch of normalized articles and return how many were processed.
     *
     * @param  iterable<ArticleData>  $articles
     */
    public function import(iterable $articles): int
    {
        $count = 0;

        foreach ($articles as $article) {
            $this->store($article);
            $count++;
        }

        // TODO: would be nicer to upsert in chunks instead of one query per article
        return $count;
    }

    private function store(ArticleData $data): void
    {
        Article::updateOrCreate(
            // external id is sometimes the full url which is too long to index nicely,
            // so we just hash it to a fixed length.
            ['provider' => $data->provider, 'external_id' => hash('sha256', $data->externalId)],
            [
                'source_id' => $this->resolveSource($data->sourceName),
                'category_id' => $this->resolveCategory($data->categoryName),
                'author_id' => $this->resolveAuthor($data->authorName),
                'title' => $data->title,
                'description' => $data->description,
                'content' => $data->content,
                'url' => $data->url,
                'image_url' => $data->imageUrl,
                'published_at' => $data->publishedAt,
            ],
        );
    }

    private function resolveSource(string $name): int
    {
        $slug = Str::slug($name);

        return $this->sources[$slug] ??= Source::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
    }

    private function resolveCategory(?string $name): ?int
    {
        if (blank($name)) {
            return null;
        }

        $slug = Str::slug($name);

        return $this->categories[$slug] ??= Category::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
    }

    private function resolveAuthor(?string $name): ?int
    {
        if (blank($name)) {
            return null;
        }

        $slug = Str::slug($name);

        return $this->authors[$slug] ??= Author::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
    }
}
