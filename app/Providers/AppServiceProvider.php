<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\AssetAcquisitionService::class);
        $this->app->singleton(\App\Services\ManifestGeneratorService::class);

        // QuizGeneratorService butuh ManifestGeneratorService + AssetAcquisitionService
        $this->app->singleton(\App\Services\QuizGeneratorService::class, function ($app) {
            return new \App\Services\QuizGeneratorService(
                manifest:    $app->make(\App\Services\ManifestGeneratorService::class),
                acquisition: $app->make(\App\Services\AssetAcquisitionService::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_contains(request()->getHost(), 'trycloudflare.com')) {
            URL::forceScheme('https');
        }
        Model::preventLazyLoading(!app()->isProduction());
    }
}