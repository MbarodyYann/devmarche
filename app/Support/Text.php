<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Extraits de texte : le même nettoyage partout (cartes, modération, meta description).
 * Retire le HTML et la syntaxe Markdown, puis coupe proprement.
 */
class Text
{
    /** Texte brut à partir de HTML ou de Markdown. */
    public static function plain(?string $text): string
    {
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $text = preg_replace([
            '/```[^\n]*\n(.*?)```/s',          // blocs de code : on garde le code, sans les clôtures
            '/!\[([^\]]*)\]\([^)]*\)/',        // images : le texte alternatif
            '/\[([^\]]+)\]\([^)]*\)/',         // liens : le texte du lien
            '/`([^`]*)`/',                     // code en ligne
            '/(\*\*|__)(.+?)\1/s',             // gras
            '/(?<![\w*])([*_])(?!\s)(.+?)(?<!\s)\1(?![\w*])/s', // italique
            '/^\s{0,3}#{1,6}\s+/m',            // titres
            '/^\s{0,3}>\s?/m',                 // citations
            '/^\s*(?:[-*+]|\d+[.)])\s+/m',     // listes
            '/^\s*(?:-{3,}|\*{3,}|_{3,})\s*$/m', // séparateurs
        ], ['$1', '$1', '$1', '$1', '$2', '$2', '', '', '', ''], $text);

        return Str::squish($text);
    }

    /** Extrait d'au plus $length caractères, coupé proprement avec « … ». */
    public static function excerpt(?string $text, int $length = 160): string
    {
        $plain = self::plain($text);

        return mb_strlen($plain) <= $length ? $plain : rtrim(mb_substr($plain, 0, $length - 1)).'…';
    }
}
