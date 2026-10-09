<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Devs\Directory;
use Illuminate\Support\Facades\Route;

// L'accueil arrive au lot 4 : en attendant, on ouvre sur l'annuaire.
Route::redirect('/', '/devs')->name('home');

Route::get('/devs', Directory::class)->name('devs.index');
Route::get('/devs/{user}', [ProfileController::class, 'show'])->name('devs.show');

Route::middleware('auth')->group(function () {
    Route::get('/devs/{user}/modifier', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/devs/{user}', [ProfileController::class, 'update'])
        ->middleware('throttle:20,1')
        ->name('profile.update');
});

// Rubriques des lots 2 et 3 (missions, questions-réponses).
Route::view('/missions', 'coming-soon', [
    'title' => 'Missions',
    'message' => 'Les missions arrivent bientôt.',
])->name('missions.index');

Route::view('/questions', 'coming-soon', [
    'title' => 'Questions-réponses',
    'message' => 'Les questions-réponses arrivent bientôt.',
])->name('questions.index');
