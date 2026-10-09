<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, fn () => ImageManager::gd());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        // Les identifiants sont numériques : évite une erreur de cast PostgreSQL sur /devs/abc.
        Route::pattern('user', '[0-9]+');
        Route::pattern('mission', '[0-9]+');
        Route::pattern('application', '[0-9]+');

        // Limites anti-abus : publication de missions et candidatures, par membre.
        RateLimiter::for('missions', fn (Request $request) => Limit::perHour(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('applications', fn (Request $request) => Limit::perHour(30)->by($request->user()?->id ?: $request->ip()));

        Carbon::setLocale(config('app.locale'));
    }
}
