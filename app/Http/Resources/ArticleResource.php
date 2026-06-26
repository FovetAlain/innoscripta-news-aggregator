<?php

namespace App\Http\Resources;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Article
 */
class ArticleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'url' => $this->url,
            'image_url' => $this->image_url,
            'provider' => $this->provider->value,
            'published_at' => $this->published_at?->toIso8601String(),
            'source' => new SourceResource($this->source),
            'category' => $this->category ? new CategoryResource($this->category) : null,
            'author' => $this->author ? new AuthorResource($this->author) : null,
        ];
    }
}
