<?php

namespace App\Providers;

use App\Services\News\Providers\GuardianProvider;
use App\Services\News\Providers\NewsApiProvider;
use App\Services\News\Providers\NytProvider;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    /**
     * Every provider is tagged so the importer can iterate them without
     * knowing the concrete classes. Register a new source here and you're done.
     */
    public const TAG = 'news.providers';

    public function register(): void
    {
        $this->app->tag([
            NewsApiProvider::class,
            GuardianProvider::class,
            NytProvider::class,
        ], self::TAG);
    }
}
