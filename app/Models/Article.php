<?php

namespace App\Models;

use App\Enums\Provider;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'source_id', 'category_id', 'author_id',
    'provider', 'external_id',
    'title', 'description', 'content', 'url', 'image_url', 'published_at',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'provider' => Provider::class,
            'published_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    // dumb LIKE search over title + description. fine for now, swap for
    // fulltext/scout if it ever gets slow.
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function (Builder $query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * @param  list<int>  $ids
     */
    public function scopeForSources(Builder $query, array $ids): void
    {
        $query->whereIn('source_id', $ids);
    }

    /**
     * @param  list<int>  $ids
     */
    public function scopeForCategories(Builder $query, array $ids): void
    {
        $query->whereIn('category_id', $ids);
    }

    /**
     * @param  list<int>  $ids
     */
    public function scopeForAuthors(Builder $query, array $ids): void
    {
        $query->whereIn('author_id', $ids);
    }

    public function scopePublishedAfter(Builder $query, string $date): void
    {
        $query->where('published_at', '>=', $date);
    }

    public function scopePublishedBefore(Builder $query, string $date): void
    {
        $query->where('published_at', '<=', $date);
    }
}
