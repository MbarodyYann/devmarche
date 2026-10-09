<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\DemoLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MissionApplicationController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MissionReviewController;
use App\Http\Controllers\MissionStatusController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Devs\Directory;
use App\Livewire\Missions\Index as MissionIndex;
use App\Livewire\Questions\Create as QuestionCreate;
use App\Livewire\Questions\Index as QuestionIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// À propos (projet, équipe, stack, licences, usage de l'IA)
Route::view('/a-propos', 'about')->name('about');

// SEO : plan du site et consignes aux robots (dynamiques pour donner l'URL absolue du plan)
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Démo pour le jury : connexion en un clic (404 si APP_DEMO=false)
Route::post('/demo/{persona}', DemoLoginController::class)
    ->middleware(['guest', 'throttle:demo'])
    ->where('persona', '[a-z]+')
    ->name('demo.login');

// Missions
Route::get('/missions', MissionIndex::class)->name('missions.index');
Route::get('/missions/{mission}', [MissionController::class, 'show'])->name('missions.show');

Route::middleware('auth')->group(function () {
    Route::get('/missions/publier', [MissionController::class, 'create'])->name('missions.create');
    Route::post('/missions', [MissionController::class, 'store'])->middleware('throttle:missions')->name('missions.store');
    Route::post('/missions/{mission}/candidatures', [MissionApplicationController::class, 'store'])
        ->middleware('throttle:applications')
        ->name('missions.apply');
    // Retenir un dev = sécuriser les fonds (paiement Mobile Money simulé), en deux étapes.
    Route::get('/missions/{mission}/candidatures/{application}/paiement', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/missions/{mission}/candidatures/{application}/paiement', [PaymentController::class, 'store'])->middleware('throttle:20,1')->name('payments.store');
    Route::get('/missions/{mission}/candidatures/{application}/paiement/confirmation', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/missions/{mission}/candidatures/{application}/paiement/confirmation', [PaymentController::class, 'verify'])->middleware('throttle:20,1')->name('payments.verify');
    Route::post('/missions/{mission}/terminer', [MissionStatusController::class, 'complete'])->name('missions.complete');
    Route::post('/missions/{mission}/annuler', [MissionStatusController::class, 'cancel'])->name('missions.cancel');
    Route::post('/missions/{mission}/avis', [MissionReviewController::class, 'store'])->middleware('throttle:20,1')->name('missions.review');
});

// Devs
Route::get('/devs', Directory::class)->name('devs.index');
Route::get('/devs/{user}', [ProfileController::class, 'show'])->name('devs.show');

Route::middleware('auth')->group(function () {
    Route::get('/devs/{user}/modifier', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/devs/{user}', [ProfileController::class, 'update'])
        ->middleware('throttle:20,1')
        ->name('profile.update');
});

// Questions-réponses
Route::get('/questions', QuestionIndex::class)->name('questions.index');
Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');

Route::middleware('auth')->group(function () {
    Route::get('/questions/poser', QuestionCreate::class)->name('questions.create');
    Route::post('/questions/{question}/reponses', [AnswerController::class, 'store'])
        ->middleware('throttle:answers')
        ->name('answers.store');
    Route::post('/questions/{question}/reponses/{answer}/accepter', [AnswerController::class, 'accept'])->name('answers.accept');
});

// Notifications
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/lire', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])
        ->whereUuid('notification')
        ->name('notifications.open');
});
