<?php

namespace App\Console\Commands;

use App\Support\Demo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Remet la démo du jury à zéro (planifiée chaque nuit à 3 h, routes/console.php).
 * Refusée hors mode démo : elle efface toute la base.
 */
class ResetDemo extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Réinitialise les données de démonstration (APP_DEMO=true seulement)';

    public function handle(): int
    {
        if (! Demo::enabled()) {
            $this->error('Refusé : demo:reset efface toute la base et n\'est autorisée qu\'avec APP_DEMO=true.');

            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
        Cache::forget('home.stats');

        $this->info('Démo réinitialisée.');

        return self::SUCCESS;
    }
}
