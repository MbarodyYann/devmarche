<?php

namespace App\Support;

/**
 * Textes répétés mot pour mot sur le site (11-redaction.md, « Les deux CTA du site »).
 * Les modifier ici les modifie partout.
 */
class Copy
{
    /** CTA principal unique d'un visiteur : navigation, hero, en-têtes, inscription, CTA final. */
    public const SIGNUP_CTA = 'Créer mon profil gratuit';

    /** Lien discret pour le visiteur qui recrute, sous chaque CTA principal visiteur. */
    public const RECRUIT_LINK = 'Vous recrutez ? Publier une mission →';

    /** Réassurance sous chaque CTA principal visiteur (affichée avec une coche devant chaque élément). */
    public const SIGNUP_REASSURANCE = [self::REASSURANCE_RISK, self::REASSURANCE_NEXT];

    public const REASSURANCE_RISK = 'Gratuit, sans carte bancaire';

    public const REASSURANCE_NEXT = 'Profil en ligne en 2 minutes';

    /** CTA principal d'un membre connecté sur l'accueil. */
    public const MEMBER_CTA = 'Voir les missions pour moi';
}
