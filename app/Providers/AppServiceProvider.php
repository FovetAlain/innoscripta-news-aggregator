<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // General API budget: per authenticated user, or per IP for guests.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));

        // Tighter budget on the credential endpoints to slow down brute forcing.
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)
            ->by($request->input('email').'|'.$request->ip()));
    }
}
