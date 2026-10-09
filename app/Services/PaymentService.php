<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\Mission;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Séquestre Mobile Money SIMULÉ : aucun argent réel n'est débité, rien n'est envoyé à un service externe.
 * À l'attribution, le client sécurise le montant proposé par le dev ; il est libéré à la clôture,
 * remboursé si la mission est annulée.
 */
class PaymentService
{
    public function __construct(private readonly MissionService $missions) {}

    /** Sécurise les fonds puis retient le dev, dans la même transaction. */
    public function secure(Mission $mission, Application $application, string $provider, string $phone): Payment
    {
        return DB::transaction(function () use ($mission, $application, $provider, $phone) {
            $this->missions->accept($mission, $application);

            $payment = new Payment([
                'provider' => $provider,
                'phone' => preg_replace('/\D/', '', $phone),
                'amount_fcfa' => $application->proposed_fcfa,
                'status' => PaymentStatus::Held,
                'reference' => Payment::newReference(),
            ]);
            $payment->mission()->associate($mission);
            $payment->save();

            return $payment;
        });
    }

    public static function release(Mission $mission): void
    {
        $mission->payment()->where('status', PaymentStatus::Held)->update(['status' => PaymentStatus::Released, 'updated_at' => now()]);
    }

    public static function refund(Mission $mission): void
    {
        $mission->payment()->where('status', PaymentStatus::Held)->update(['status' => PaymentStatus::Refunded, 'updated_at' => now()]);
    }
}
