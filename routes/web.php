<?php

use App\Http\Controllers\MissionApplicationController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MissionStatusController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Devs\Directory;
use App\Livewire\Missions\Index as MissionIndex;
use Illuminate\Support\Facades\Route;

// L'accueil (hero) arrive au point 13 du lot 2 : en attendant, on ouvre sur les missions.
Route::redirect('/', '/missions')->name('home');

// Missions
Route::get('/missions', MissionIndex::class)->name('missions.index');
Route::get('/missions/{mission}', [MissionController::class, 'show'])->name('missions.show');

Route::middleware('auth')->group(function () {
    Route::get('/missions/publier', [MissionController::class, 'create'])->name('missions.create');
    Route::post('/missions', [MissionController::class, 'store'])->middleware('throttle:missions')->name('missions.store');
    Route::post('/missions/{mission}/candidatures', [MissionApplicationController::class, 'store'])
        ->middleware('throttle:applications')
        ->name('missions.apply');
    Route::post('/missions/{mission}/candidatures/{application}/retenir', [MissionApplicationController::class, 'accept'])
        ->name('missions.accept');
    Route::post('/missions/{mission}/terminer', [MissionStatusController::class, 'complete'])->name('missions.complete');
    Route::post('/missions/{mission}/annuler', [MissionStatusController::class, 'cancel'])->name('missions.cancel');
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

// Rubrique du lot 3 (questions-réponses).
Route::view('/questions', 'coming-soon', [
    'title' => 'Questions-réponses',
    'message' => 'Les questions-réponses arrivent bientôt.',
])->name('questions.index');
