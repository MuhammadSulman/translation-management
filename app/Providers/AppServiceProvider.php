<?php

namespace App\Providers;

use App\Contracts\TranslationCacheContract;
use App\Contracts\TranslationSearchContract;
use App\Services\TranslationCacheService;
use App\Services\TranslationSearchService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

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
        JsonResource::withoutWrapping();

        Model::preventLazyLoading(!$this->app->isProduction());

        $this->app->singleton(abstract: TranslationCacheContract::class, concrete: TranslationCacheService::class);
        $this->app->singleton(abstract: TranslationSearchContract::class, concrete: TranslationSearchService::class);
    }
}
