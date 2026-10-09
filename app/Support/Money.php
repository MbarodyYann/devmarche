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
}
