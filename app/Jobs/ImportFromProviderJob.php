<?php

namespace App\Jobs;

use App\Services\News\ArticleImporter;
use App\Services\News\Contracts\NewsProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportFromProviderJob implements ShouldQueue
{
    use Queueable;

    /**
     * We carry the class name (not the instance) so the job stays cheap to
     * serialize; the provider is resolved fresh from the container on handle.
     *
     * @param  class-string<NewsProvider>  $providerClass
     */
    public function __construct(public string $providerClass) {}

    public function handle(ArticleImporter $importer): void
    {
        $provider = app($this->providerClass);

        $count = $importer->import($provider->fetchLatest());

        Log::info('Articles imported', [
            'provider' => $provider->provider()->value,
            'count' => $count,
        ]);
    }
}
