<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed realistic demo data so the API can be explored without any provider keys.
     */
    public function run(): void
    {
        $sources = collect([
            'The Guardian', 'The New York Times', 'BBC News', 'TechCrunch', 'Reuters',
            'Associated Press', 'Bloomberg', 'The Verge', 'Wired', 'CNN',
        ])->map(fn (string $name) => Source::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]));

        $categories = collect([
            'Technology', 'Business', 'Sports', 'Health', 'Science', 'World', 'Politics', 'Entertainment',
        ])->map(fn (string $name) => Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]));

        $authors = Author::factory()->count(25)->create();

        // recycle() makes the article factory reuse the sets above instead of creating new ones.
        Article::factory()
            ->count(150)
            ->recycle($sources)
            ->recycle($categories)
            ->recycle($authors)
            ->create();

        $demo = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo User', 'password' => 'password'],
        );

        $demo->preference()->firstOrCreate([], [
            'preferred_sources' => $sources->take(2)->pluck('id')->all(),
            'preferred_categories' => $categories->take(3)->pluck('id')->all(),
        ]);
    }
}
