<?php

namespace App\Providers;

use App\Models\Answer;
use App\Models\Mission;
use App\Models\Question;
use App\Models\Review;
use App\Models\User;
use App\Policies\VotePolicy;
use App\Services\MarkdownRenderer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
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
        $this->app->singleton(MarkdownRenderer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        // Noms stables dans les colonnes polymorphes (votes, événements de réputation).
        Relation::enforceMorphMap([
            'question' => Question::class,
            'answer' => Answer::class,
            'mission' => Mission::class,
            'review' => Review::class,
            'user' => User::class,
        ]);

        // Les identifiants sont numériques : évite une erreur de cast PostgreSQL sur /devs/abc.
        Route::pattern('user', '[0-9]+');
        Route::pattern('mission', '[0-9]+');
        Route::pattern('review', '[0-9]+');
        Route::pattern('application', '[0-9]+');
        Route::pattern('question', '[0-9]+');
        Route::pattern('answer', '[0-9]+');

        // Le vote porte sur une question OU une réponse : on déclare la policy explicitement.
        Gate::define('vote', [VotePolicy::class, 'vote']);

        // Limites anti-abus : publication de missions et candidatures, par membre.
        RateLimiter::for('missions', fn (Request $request) => Limit::perHour(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('questions', fn (Request $request) => Limit::perHour(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('answers', fn (Request $request) => Limit::perHour(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('votes', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('demo', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('applications', fn (Request $request) => Limit::perHour(30)->by($request->user()?->id ?: $request->ip()));

        Carbon::setLocale(config('app.locale'));
    }
}
