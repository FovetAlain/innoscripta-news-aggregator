<?php

namespace App\Console\Commands;

use App\Jobs\ImportFromProviderJob;
use App\Providers\NewsServiceProvider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('news:fetch {--sync : Import inline instead of dispatching to the queue}')]
#[Description('Fetch the latest articles from every configured news provider')]
class FetchNewsCommand extends Command
{
    public function handle(): int
    {
        $sync = $this->option('sync');

        foreach (app()->tagged(NewsServiceProvider::TAG) as $provider) {
            $job = new ImportFromProviderJob($provider::class);

            $sync ? dispatch_sync($job) : dispatch($job);

            $this->info(sprintf('%s %s', $sync ? 'Imported' : 'Queued', $provider->provider()->label()));
        }

        return self::SUCCESS;
    }
}
