<?php

namespace Database\Factories;

use App\Enums\Provider;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'source_id' => Source::factory(),
            'category_id' => Category::factory(),
            'author_id' => Author::factory(),
            'provider' => fake()->randomElement(Provider::cases()),
            'external_id' => fake()->unique()->uuid(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'url' => fake()->unique()->url(),
            'image_url' => fake()->imageUrl(),
            'published_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
