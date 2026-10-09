<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Petites règles de typographie française.
 */
class French
{
    /** Mots en h muet (élision) : liste simple, à compléter au besoin. Les autres h sont aspirés. */
    private const MUTE_H = ['hélène', 'hervé', 'hugo', 'henri', 'homme', 'hôtel', 'heure', 'histoire', 'habitude', 'hiver'];

    /**
     * « de » élidé devant une voyelle (accentuée ou non) ou un h muet :
     * de('Abdoul Kaboré') → « d'Abdoul Kaboré », de('Moussa') → « de Moussa ».
     */
    public static function de(string $word): string
    {
        return (self::elides($word) ? "d'" : 'de ').$word;
    }

    /** Le mot commence-t-il par une voyelle ou un h muet ? (Y est traité comme une consonne : « de Yaya ».) */
    public static function elides(string $word): bool
    {
        $plain = Str::lower(Str::ascii(trim($word)));

        if ($plain === '') {
            return false;
        }

        if (in_array($plain[0], ['a', 'e', 'i', 'o', 'u'], true)) {
            return true;
        }

        $first = Str::lower(Str::before(trim($word), ' '));

        return $plain[0] === 'h' && in_array($first, self::MUTE_H, true);
    }
}
