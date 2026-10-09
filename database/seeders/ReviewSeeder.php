<?php

namespace Database\Seeders;

use App\Enums\MissionStatus;
use App\Models\Mission;
use App\Services\ReviewService;
use Database\Seeders\Concerns\Timeline;
use Illuminate\Database\Seeder;

/**
 * Avis des clients sur les missions terminées : écrits à la main, variés, datés quelques heures
 * après la fin de la mission et avant la dernière activité du client.
 */
class ReviewSeeder extends Seeder
{
    use Timeline;

    /**
     * [note globale, communication, qualité, délais, commentaire].
     *
     * @var list<array{0: int, 1: int, 2: int, 3: int, 4: string}>
     */
    private const REVIEWS = [
        [5, 5, 5, 5, "Travail propre et bien documenté. Il a pris le temps de nous expliquer chaque choix technique à l'équipe."],
        [5, 5, 5, 4, "Très réactif sur WhatsApp, même le week-end. Une petite journée de retard sur la fin, mais prévenue à l'avance."],
        [4, 4, 5, 4, 'Le code est solide. Il faut parfois relancer pour avoir des nouvelles, mais le résultat est là.'],
        [5, 5, 5, 5, "Exactement ce qu'on attendait, livré en avance. On a déjà une deuxième mission en tête pour lui."],
        [5, 4, 5, 5, "A trouvé un problème de sécurité qu'on n'avait pas vu. Rigueur impressionnante."],
        [4, 5, 4, 4, 'Bonne communication du début à la fin. Quelques retouches demandées après la livraison, faites sans discuter.'],
        [5, 5, 5, 5, 'Clair, ponctuel, et de bons conseils au-delà de la mission. Je recommande sans hésiter.'],
        [3, 3, 4, 2, "La qualité est correcte, mais les délais ont glissé d'une semaine. Communication à améliorer."],
        [5, 5, 4, 5, "Il a su s'adapter quand nous avons changé d'avis en cours de route. Très professionnel."],
        [4, 4, 4, 5, 'Livré dans les temps, comme promis. La documentation pourrait être un peu plus détaillée.'],
        [5, 5, 5, 5, 'Notre équipe reprend le code sans difficulté. Tests compris, ce qui est rare.'],
        [5, 4, 5, 5, "Excellent sur la partie technique. Il pose beaucoup de questions au début, et c'est ce qui fait la différence."],
        [4, 5, 4, 3, 'Agréable à travailler, bonnes idées. Deux jours de retard, rattrapés par la qualité du rendu.'],
        [5, 5, 5, 4, 'Il connaît très bien les contraintes du Mobile Money. On a gagné un temps précieux.'],
        [5, 5, 5, 5, 'Deuxième mission avec lui, toujours aussi fiable.'],
        [4, 4, 5, 4, "Résultat de qualité. Prévoir un point hebdomadaire pour suivre l'avancement, ça aide."],
    ];

    public function __construct(private readonly ReviewService $reviews) {}

    public function run(): void
    {
        $missions = Mission::query()
            ->where('status', MissionStatus::Completed)
            ->whereDoesntHave('review')
            ->with('client')
            ->orderBy('completed_at')
            ->get();

        foreach ($missions as $i => $mission) {
            [$rating, $communication, $quality, $timeliness, $comment] = self::REVIEWS[(crc32($mission->title) + $i) % count(self::REVIEWS)];

            // Quelques heures après la fin, et avant la dernière activité prévue du client.
            $reviewedAt = $this->byActor(
                $mission->client,
                $this->clamp($mission->completed_at->copy()->addHours(3 + $i % 20)),
                $mission->completed_at->copy()->addMinutes(10),
            );

            $this->travelTo($reviewedAt, fn () => $this->reviews->submit($mission, $mission->client, [
                'rating' => $rating,
                'communication' => $communication,
                'quality' => $quality,
                'timeliness' => $timeliness,
                'comment' => $comment,
            ]));
        }
    }
}
