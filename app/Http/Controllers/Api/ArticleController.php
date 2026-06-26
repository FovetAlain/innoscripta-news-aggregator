<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleIndexRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function index(ArticleIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $articles = Article::query()
            ->with(['source', 'category', 'author'])
            ->when($filters['q'] ?? null, fn (Builder $query, string $term) => $query->search($term))
            ->when($filters['sources'] ?? null, fn (Builder $query, array $ids) => $query->forSources($ids))
            ->when($filters['categories'] ?? null, fn (Builder $query, array $ids) => $query->forCategories($ids))
            ->when($filters['authors'] ?? null, fn (Builder $query, array $ids) => $query->forAuthors($ids))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->publishedAfter($date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->publishedBefore($date))
            ->latest('published_at')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return ArticleResource::collection($articles);
    }

    public function show(Article $article): ArticleResource
    {
        return new ArticleResource($article->load(['source', 'category', 'author']));
    }

    // personalised feed - filter articles down to the user's prefs.
    // if they haven't set any prefs yet we just hand back the latest articles.
    public function feed(Request $request): AnonymousResourceCollection
    {
        $preferences = $request->user()->preference;

        // FIXME: this is basically index() minus the request filters, could probably
        // share the base query between the two at some point

        $articles = Article::query()
            ->with(['source', 'category', 'author'])
            ->when($preferences, fn (Builder $query, UserPreference $preferences) => $query->where(
                fn (Builder $query) => $query
                    ->when($preferences->preferred_sources, fn (Builder $q, array $ids) => $q->orWhereIn('source_id', $ids))
                    ->when($preferences->preferred_categories, fn (Builder $q, array $ids) => $q->orWhereIn('category_id', $ids))
                    ->when($preferences->preferred_authors, fn (Builder $q, array $ids) => $q->orWhereIn('author_id', $ids))
            ))
            ->latest('published_at')
            ->paginate(15);

        return ArticleResource::collection($articles);
    }
}
