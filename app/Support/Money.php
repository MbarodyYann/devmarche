<?php

namespace App\Support;

class Money
{
    /**
     * « 150000 » → « 150 000 » (espace fine insécable comme séparateur de milliers).
     */
    public static function fcfa(int $amount): string
    {
        return number_format($amount, 0, ',', "\u{202F}");
    }

    /** Tranches de budget affichées sur les avis (le montant exact reste entre le client et le dev). */
    public const BUDGET_RANGES = [50_000, 100_000, 250_000, 500_000, 1_000_000];

    /**
     * « 180000 » → « 100 000 – 250 000 FCFA ».
     */
    public static function budgetRange(int $amount): string
    {
        $lower = 0;
        foreach (self::BUDGET_RANGES as $upper) {
            if ($amount < $upper) {
                return $lower === 0
                    ? 'Moins de '.self::fcfa($upper).' FCFA'
                    : self::fcfa($lower).' – '.self::fcfa($upper).' FCFA';
            }
            $lower = $upper;
        }

        return 'Plus de '.self::fcfa($lower).' FCFA';
    }
}
