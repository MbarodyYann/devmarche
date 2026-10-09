<?php

namespace App\Support;

use App\Models\User;

/**
 * Mode démo pour le jury (02, « Spécial jury ») : connexion en un clic avec deux comptes,
 * actif seulement si APP_DEMO=true (config devmarche.demo).
 */
class Demo
{
    public const STARTUP_EMAIL = 'aissatou@demo.devmarche.test';

    public const DEV_EMAIL = 'moussa@demo.devmarche.test';

    /**
     * Personas, dans l'ordre d'affichage.
     *
     * @var array<string, array{email: string, name: string, label: string}>
     */
    public const PERSONAS = [
        'aissatou' => ['email' => self::STARTUP_EMAIL, 'name' => 'Aïssatou', 'label' => 'Entrer comme Aïssatou (startup, Dakar)'],
        'moussa' => ['email' => self::DEV_EMAIL, 'name' => 'Moussa', 'label' => 'Entrer comme Moussa (dev, Thiès)'],
    ];

    public static function enabled(): bool
    {
        return (bool) config('devmarche.demo');
    }

    /** Prénom du persona si $user est un compte de démo (pour le bandeau), sinon null. */
    public static function personaName(?User $user): ?string
    {
        if ($user === null || ! self::enabled()) {
            return null;
        }

        return collect(self::PERSONAS)->firstWhere('email', $user->email)['name'] ?? null;
    }
}
