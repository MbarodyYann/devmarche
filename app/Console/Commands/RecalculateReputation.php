<?php

namespace App\Console\Commands;

use App\Services\ReputationService;
use Illuminate\Console\Command;

class RecalculateReputation extends Command
{
    protected $signature = 'reputation:recalculate';

    protected $description = 'Recalcule la réputation de chaque membre à partir de reputation_events';

    public function handle(ReputationService $reputation): int
    {
        $fixed = $reputation->recalculate();

        $this->info($fixed === 0
            ? 'Toutes les réputations sont cohérentes.'
            : "$fixed réputation(s) corrigée(s).");

        return self::SUCCESS;
    }
}
