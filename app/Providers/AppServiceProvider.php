<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
{
    // Memaksa skema HTTPS jika diakses melalui Cloudflare
    if (str_contains(request()->getHost(), 'trycloudflare.com')) {
        URL::forceScheme('https');
    }
}
}
