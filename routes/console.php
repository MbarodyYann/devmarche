<?php

use Illuminate\Support\Facades\Schedule;

// Démo du jury : données remises à zéro chaque nuit (la commande refuse de tourner hors APP_DEMO).
Schedule::command('demo:reset')->dailyAt('03:00')->timezone('Africa/Dakar')->withoutOverlapping();
