<?php

namespace App\Support;

use Illuminate\Support\Str;

class Seo
{
    public const DEFAULT_DESCRIPTION = 'La plateforme d\'échange par et pour les développeurs d\'Afrique francophone : missions payées en FCFA, questions-réponses et annuaire de devs.';

    /** Extrait d'au plus $length caractères, sans balises ni retours à la ligne, coupé proprement. */
    public static function excerpt(?string $text, int $length = 160): string
    {
        $plain = Str::squish(strip_tags((string) $text));

        if ($plain === '') {
            return self::DEFAULT_DESCRIPTION;
        }

        return mb_strlen($plain) <= $length ? $plain : rtrim(mb_substr($plain, 0, $length - 1)).'…';
    }
}
