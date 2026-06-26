<?php

use App\Enums\Provider;

return [

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Credentials and endpoints for each upstream news API. A provider with an
    | empty key is simply skipped at fetch time, so the app boots fine without
    | every key being present.
    |
    */

    'providers' => [

        Provider::NewsApi->value => [
            'key' => env('NEWSAPI_KEY'),
            'base_url' => 'https://newsapi.org/v2',
            // NewsAPI doesn't return a category, so we query each one and tag the result.
            'categories' => ['business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology'],
        ],

        Provider::Guardian->value => [
            'key' => env('GUARDIAN_KEY'),
            'base_url' => 'https://content.guardianapis.com',
        ],

        Provider::Nyt->value => [
            'key' => env('NYT_KEY'),
            'base_url' => 'https://api.nytimes.com/svc',
        ],

    ],

    // Number of articles requested per source on each run.
    'page_size' => (int) env('NEWS_PAGE_SIZE', 50),

];
