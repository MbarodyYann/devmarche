<?php

namespace App\Support;

class Seo
{
    public const DEFAULT_DESCRIPTION = 'La plateforme d\'échange par et pour les développeurs d\'Afrique francophone : missions payées en FCFA, questions-réponses et annuaire de devs.';

    /** Meta description : extrait nettoyé (HTML et Markdown retirés), 160 caractères au plus. */
    public static function excerpt(?string $text, int $length = 160): string
    {
        $excerpt = Text::excerpt($text, $length);

        return $excerpt === '' ? self::DEFAULT_DESCRIPTION : $excerpt;
    }
}
