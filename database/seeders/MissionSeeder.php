<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Mission;
use App\Models\User;
use App\Services\MissionService;
use App\Services\ReputationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 25 missions crédibles, dans tous les statuts, avec leurs candidatures.
 * Les missions terminées créditent les freelances via ReputationService (événements mission_completed),
 * puis un historique de démonstration donne des cauris variés (0 pour les nouveaux, ~1 500 pour les meilleurs).
 *
 * Pour le scénario de démo, la mission Wave d'Aïssatou reste OUVERTE.
 */
class MissionSeeder extends Seeder
{
    public function __construct(
        private readonly MissionService $missions,
        private readonly ReputationService $reputation,
    ) {}

    public function run(): void
    {
        if (Mission::query()->exists()) {
            return; // déjà semé : on ne duplique pas
        }

        $users = User::query()->get()->keyBy('name');
        $user = fn (string $name): User => $users[$name] ?? throw new \RuntimeException("Dev inconnu dans les seeders : $name");

        DB::transaction(function () use ($user) {
            foreach ($this->missions() as $data) {
                $createdAt = Carbon::now()->subDays($data['days'])->setTime(9 + $data['days'] % 9, 15);

                $mission = $this->missions->publish($user($data['client']), [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'budget_fcfa' => $data['budget'],
                    'country' => $data['country'],
                    'deadline' => isset($data['deadline']) ? now()->addDays($data['deadline'])->toDateString() : null,
                ], $data['skills']);
                $mission->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

                $applications = [];
                foreach ($data['applicants'] ?? [] as $i => $name) {
                    $application = $mission->applications()->make([
                        'message' => $this->message($data, $i),
                        'proposed_fcfa' => (int) round($data['budget'] * [1, 0.9, 1.1, 0.95, 1.05][$i % 5] / 1000) * 1000,
                    ]);
                    $application->user()->associate($user($name));
                    $application->save();
                    $application->forceFill(['created_at' => $createdAt->copy()->addHours(3 + $i * 5)])->save();
                    $applications[$name] = $application;
                }

                match ($data['status']) {
                    'assigned' => $this->missions->accept($mission, $applications[$data['freelancer']]),
                    'completed' => $this->complete($mission, $applications[$data['freelancer']], $createdAt),
                    'cancelled' => $this->missions->cancel($mission),
                    default => null,
                };
            }

            // Historique de démonstration (Q&R et missions antérieures au lancement) : des cauris variés et crédibles.
            foreach ($this->history() as $name => $points) {
                $this->reputation->award($user($name), $points, 'seed_history');
            }
        });
    }

    private function complete(Mission $mission, Application $application, Carbon $createdAt): void
    {
        $this->missions->accept($mission, $application);
        $this->missions->complete($mission);
        $mission->forceFill([
            'assigned_at' => $createdAt->copy()->addDays(2),
            'completed_at' => $createdAt->copy()->addDays(9),
        ])->save();
    }

    private function message(array $data, int $i): string
    {
        $openers = [
            'Bonjour, j\'ai déjà réalisé un projet très proche l\'an dernier et je peux vous montrer le code.',
            'Bonjour ! Votre besoin est clair, je peux démarrer dès lundi et livrer par étapes.',
            'Bonsoir, je travaille exactement sur cette stack au quotidien. Je propose un premier point de 30 minutes.',
            'Bonjour, mission intéressante. Je suis disponible à temps plein les trois prochaines semaines.',
            'Salam, je connais bien ce type d\'intégration et les contraintes de connexion locales.',
        ];

        return $openers[$i % 5].' Compétences utiles ici : '.implode(', ', $data['skills']).'.';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function missions(): array
    {
        return [
            // La mission de la démo : ouverte, publiée par Aïssatou.
            [
                'client' => 'Aïssatou Diallo', 'status' => 'open', 'days' => 1, 'deadline' => 21,
                'title' => 'Intégration de l\'API de paiement Wave pour une boutique en ligne',
                'budget' => 150000, 'country' => 'SN', 'skills' => ['Wave API', 'Laravel', 'PHP'],
                'description' => "Notre boutique Laravel encaisse aujourd'hui uniquement à la livraison. Nous voulons ajouter le paiement Wave : création de la session de paiement, redirection, vérification de la signature du webhook et mise à jour de la commande.\n\nLivrables : le code intégré à notre dépôt, des tests, et une courte documentation pour l'équipe.",
                'applicants' => ['Rodrigue Hounsou', 'Edem Afenyo'],
            ],
            [
                'client' => 'Aïssatou Diallo', 'status' => 'completed', 'freelancer' => 'Moussa Ndiaye', 'days' => 34,
                'title' => 'Tableau de bord des encaissements Orange Money',
                'budget' => 220000, 'country' => 'SN', 'skills' => ['Laravel', 'Orange Money', 'PostgreSQL'],
                'description' => "Un tableau de bord interne pour suivre les encaissements Orange Money de nos commerçants : totaux par jour, par point de vente, export CSV et alerte en cas d'écart avec le relevé.",
                'applicants' => ['Moussa Ndiaye', 'Boukary Ouédraogo', 'Paul Fotso'],
            ],
            [
                'client' => 'Kossi Mensah', 'status' => 'open', 'days' => 2, 'deadline' => 30,
                'title' => 'Appli Flutter de suivi de tontine',
                'budget' => 350000, 'country' => 'TG', 'skills' => ['Flutter', 'Firebase', 'Dart'],
                'description' => 'Une application mobile pour gérer une tontine entre amis : membres, calendrier des tours, cotisations reçues, rappels par notification. Elle doit rester utilisable hors connexion et se synchroniser dès que le réseau revient.',
                'applicants' => ['Akossiwa Agbeko', 'Fatou Sow', 'Oumou Dembélé'],
            ],
            [
                'client' => 'Kouadio Yao', 'status' => 'assigned', 'freelancer' => 'Samuel Nkwenti', 'days' => 6,
                'title' => 'Service USSD de consultation de solde',
                'budget' => 400000, 'country' => 'CI', 'skills' => ['USSD', 'Java', 'MTN MoMo API'],
                'description' => "Un service USSD (*xxx#) pour que les clients d'une microfinance consultent leur solde et leurs trois dernières opérations, sans smartphone. Connexion à notre API interne déjà en place.",
                'applicants' => ['Samuel Nkwenti', 'Abdoul Kaboré'],
            ],
            [
                'client' => 'Jean-Marc N\'Guessan', 'status' => 'open', 'days' => 3,
                'title' => 'Site vitrine pour un restaurant à Abidjan',
                'budget' => 120000, 'country' => 'CI', 'skills' => ['WordPress', 'UI/UX'],
                'description' => "Site vitrine d'un maquis moderne à Cocody : présentation, menu du jour modifiable par le gérant, réservation par WhatsApp et plan d'accès. Il doit se charger vite sur mobile.",
                'applicants' => ['Serge Kouassi', 'Seydou Touré', 'Awa Seck', 'Nadège Dossou'],
            ],
            [
                'client' => 'Cheikh Fall', 'status' => 'completed', 'freelancer' => 'Moussa Ndiaye', 'days' => 58,
                'title' => 'Migration d\'une API Laravel 8 vers Laravel 12',
                'budget' => 300000, 'country' => null, 'skills' => ['Laravel', 'PHP', 'Docker'],
                'description' => "Montée de version d'une API de facturation (Laravel 8, PHP 7.4) vers Laravel 12 et PHP 8.2, sans interruption de service. Les tests existants doivent rester verts.",
                'applicants' => ['Moussa Ndiaye', 'Fatoumata Camara'],
            ],
            [
                'client' => 'Fiacre Agossou', 'status' => 'open', 'days' => 4, 'deadline' => 45,
                'title' => 'API de transcription de messages vocaux en fon',
                'budget' => 600000, 'country' => 'BJ', 'skills' => ['Python', 'FastAPI', 'Data Science'],
                'description' => "Exposer notre modèle de reconnaissance vocale en fon derrière une API FastAPI : file d'attente, limites d'usage par clé, et tableau de suivi des temps de réponse.",
                'applicants' => ['Kadiatou Keïta', 'Arnaud Mbarga'],
            ],
            [
                'client' => 'Paul Fotso', 'status' => 'completed', 'freelancer' => 'Christelle Ngo Biyong', 'days' => 41,
                'title' => 'Application de livraison pour un pressing à Douala',
                'budget' => 450000, 'country' => 'CM', 'skills' => ['Flutter', 'Firebase'],
                'description' => 'Prise de commande de ramassage, suivi du linge et paiement à la livraison pour un pressing de Bonapriso. Une appli client et une interface simple pour les livreurs.',
                'applicants' => ['Christelle Ngo Biyong', 'Akossiwa Agbeko'],
            ],
            [
                'client' => 'Salimata Sawadogo', 'status' => 'open', 'days' => 5,
                'title' => 'Tableau de bord Power BI pour une coopérative de karité',
                'budget' => 180000, 'country' => 'BF', 'skills' => ['Power BI', 'Data Science'],
                'description' => "Relier nos fichiers de collecte (Excel) à un tableau de bord : volumes par village, prix moyen d'achat, paiements aux productrices. Former deux personnes à le mettre à jour.",
                'applicants' => ['Mariam Traoré'],
            ],
            [
                'client' => 'Aïssatou Diallo', 'status' => 'open', 'days' => 7,
                'title' => 'Maquettes Figma de l\'appli marchand',
                'budget' => 200000, 'country' => null, 'skills' => ['Figma', 'UI/UX'],
                'description' => "Douze écrans pour notre application marchand : encaissement, historique, retraits. Parcours très courts, lisibles en plein soleil, pensés pour des téléphones d'entrée de gamme.",
                'applicants' => ['Awa Seck', 'Grâce Adjovi', 'Hawa Soumah'],
            ],
            [
                'client' => 'Bakary Coulibaly', 'status' => 'assigned', 'freelancer' => 'Mamadou Bah', 'days' => 9,
                'title' => 'Webhooks Orange Money Mali pour une plateforme de commande',
                'budget' => 250000, 'country' => 'ML', 'skills' => ['Orange Money', 'Node.js'],
                'description' => "Fiabiliser la réception des notifications de paiement : vérification, idempotence, relance automatique en cas d'échec et journal consultable par le support.",
                'applicants' => ['Mamadou Bah', 'Edem Afenyo'],
            ],
            [
                'client' => 'Arnaud Mbarga', 'status' => 'completed', 'freelancer' => 'Kossi Mensah', 'days' => 70,
                'title' => 'Plateforme de réservation de salles de cours',
                'budget' => 500000, 'country' => 'CM', 'skills' => ['React', 'Node.js', 'PostgreSQL'],
                'description' => "Réservation des salles d'un institut à Yaoundé : calendrier, conflits, validation par l'administration et notifications par e-mail.",
                'applicants' => ['Kossi Mensah', 'Ibrahima Diop'],
            ],
            [
                'client' => 'Mathieu Ahouansou', 'status' => 'cancelled', 'days' => 25,
                'title' => 'Module de paie pour une ONG',
                'budget' => 380000, 'country' => 'BJ', 'skills' => ['Symfony', 'PHP'],
                'description' => 'Calcul des salaires et des retenues pour 40 employés, avec bulletins en PDF. Projet suspendu par le bailleur.',
                'applicants' => ['Rodrigue Hounsou'],
            ],
            [
                'client' => 'Grâce Adjovi', 'status' => 'open', 'days' => 2,
                'title' => 'Intégration MTN MoMo sur un site de billetterie',
                'budget' => 175000, 'country' => 'BJ', 'skills' => ['MTN MoMo API', 'Laravel'],
                'description' => "Ajouter le paiement MTN MoMo à la billetterie d'un festival à Cotonou, avec génération du billet en QR code après confirmation.",
                'applicants' => ['Rodrigue Hounsou', 'Edem Afenyo', 'Serge Kouassi'],
            ],
            [
                'client' => 'Kadiatou Keïta', 'status' => 'completed', 'freelancer' => 'Arnaud Mbarga', 'days' => 47,
                'title' => 'Outil de collecte de données terrain hors connexion',
                'budget' => 420000, 'country' => 'ML', 'skills' => ['Django', 'PostgreSQL'],
                'description' => 'Des formulaires utilisables sans réseau par des enquêteurs, synchronisés le soir. Export vers notre base Django existante.',
                'applicants' => ['Arnaud Mbarga', 'Elom Kpodar'],
            ],
            [
                'client' => 'Ousmane Sarr', 'status' => 'open', 'days' => 6,
                'title' => 'Personnalisation d\'Odoo pour une quincaillerie',
                'budget' => 260000, 'country' => 'SN', 'skills' => ['Odoo', 'Python'],
                'description' => 'Gestion des stocks multi-dépôts, ventes au comptoir et facturation en FCFA pour une quincaillerie de Thiès. Formation des deux vendeurs incluse.',
                'applicants' => [],
            ],
            [
                'client' => 'Cheikh Fall', 'status' => 'assigned', 'freelancer' => 'Yawo Amegbor', 'days' => 12,
                'title' => 'Mise en place de la supervision de nos serveurs',
                'budget' => 230000, 'country' => null, 'skills' => ['Linux', 'DevOps', 'Docker'],
                'description' => "Supervision et alertes pour six serveurs : disponibilité, disque, sauvegardes. Alertes par SMS et Telegram, documentation pour l'astreinte.",
                'applicants' => ['Yawo Amegbor', 'Jean-Marc N\'Guessan'],
            ],
            [
                'client' => 'Brenda Tchoupo', 'status' => 'open', 'days' => 8,
                'title' => 'Correction de bugs sur un site Next.js',
                'budget' => 60000, 'country' => null, 'skills' => ['Next.js', 'React'],
                'description' => "Petite mission : trois bugs d'affichage sur mobile et un formulaire de contact qui n'envoie plus rien. Idéal pour un dev junior motivé.",
                'applicants' => ['Rasmata Compaoré', 'Hawa Soumah'],
            ],
            [
                'client' => 'Mamadou Bah', 'status' => 'completed', 'freelancer' => 'Fatoumata Camara', 'days' => 64,
                'title' => 'Back-office de gestion de flotte de livreurs',
                'budget' => 340000, 'country' => 'GN', 'skills' => ['Laravel', 'Livewire'],
                'description' => 'Suivi des livreurs à moto à Conakry : affectation des courses, encaissements de la journée, classement hebdomadaire.',
                'applicants' => ['Fatoumata Camara', 'Alpha Barry'],
            ],
            [
                'client' => 'Elom Kpodar', 'status' => 'open', 'days' => 10,
                'title' => 'Application React Native de suivi de microcrédits',
                'budget' => 480000, 'country' => 'TG', 'skills' => ['React Native', 'TypeScript'],
                'description' => 'Une appli pour les agents de crédit : portefeuille de clients, échéances, encaissements en Mobile Money et rapports quotidiens.',
                'applicants' => ['Aya Koné', 'Ibrahima Diop'],
            ],
            [
                'client' => 'Boukary Ouédraogo', 'status' => 'completed', 'freelancer' => 'Abdoul Kaboré', 'days' => 80,
                'title' => 'Appli Android de prise de rendez-vous médicaux',
                'budget' => 390000, 'country' => 'BF', 'skills' => ['Kotlin', 'Android'],
                'description' => "Prise de rendez-vous dans trois centres de santé de Ouagadougou, rappels par SMS la veille et file d'attente du jour.",
                'applicants' => ['Abdoul Kaboré'],
            ],
            [
                'client' => 'Aya Koné', 'status' => 'open', 'days' => 11,
                'title' => 'Audit de sécurité d\'une API de paiement',
                'budget' => 550000, 'country' => 'CI', 'skills' => ['Node.js', 'DevOps'],
                'description' => 'Revue de sécurité de notre API (authentification, gestion des secrets, limites de débit), rapport priorisé et accompagnement pour les corrections.',
                'applicants' => ['Jean-Marc N\'Guessan'],
            ],
            [
                'client' => 'Seydou Touré', 'status' => 'completed', 'freelancer' => 'Moussa Ndiaye', 'days' => 90,
                'title' => 'Réservations en ligne pour un hôtel à Ségou',
                'budget' => 210000, 'country' => 'ML', 'skills' => ['Laravel', 'Orange Money'],
                'description' => 'Module de réservation avec acompte Orange Money et confirmation automatique par e-mail et WhatsApp.',
                'applicants' => ['Moussa Ndiaye', 'Bakary Coulibaly'],
            ],
            [
                'client' => 'Hawa Soumah', 'status' => 'cancelled', 'days' => 30,
                'title' => 'Refonte du site d\'une école à Labé',
                'budget' => 90000, 'country' => 'GN', 'skills' => ['WordPress'],
                'description' => "Refonte du site et des inscriptions en ligne. Annulée : l'école a obtenu un site offert par sa fédération.",
                'applicants' => ['Alpha Barry'],
            ],
            [
                'client' => 'Christelle Ngo Biyong', 'status' => 'open', 'days' => 13,
                'title' => 'Tests automatisés pour une appli Flutter',
                'budget' => 140000, 'country' => null, 'skills' => ['Flutter', 'Dart'],
                'description' => "Écrire les tests widget et d'intégration des parcours critiques (inscription, paiement) et les brancher dans notre intégration continue.",
                'applicants' => ['Fatou Sow'],
            ],
        ];
    }

    /**
     * Cauris de démonstration accumulés avant le lancement (remplacés au lot 3 par de vraies réponses votées).
     *
     * @return array<string, int>
     */
    private function history(): array
    {
        return [
            'Jean-Marc N\'Guessan' => 1480, 'Kouadio Yao' => 1210, 'Cheikh Fall' => 960, 'Fatoumata Camara' => 870,
            'Kossi Mensah' => 760, 'Paul Fotso' => 690, 'Arnaud Mbarga' => 620, 'Rodrigue Hounsou' => 540,
            'Christelle Ngo Biyong' => 480, 'Samuel Nkwenti' => 430, 'Fiacre Agossou' => 410, 'Mathieu Ahouansou' => 380,
            'Ibrahima Diop' => 350, 'Akossiwa Agbeko' => 320, 'Abdoul Kaboré' => 290, 'Bakary Coulibaly' => 260,
            'Kadiatou Keïta' => 240, 'Boukary Ouédraogo' => 220, 'Yawo Amegbor' => 200, 'Fatou Sow' => 190,
            'Awa Seck' => 170, 'Mamadou Bah' => 150, 'Moussa Ndiaye' => 120, 'Edem Afenyo' => 110,
            'Aya Koné' => 95, 'Salimata Sawadogo' => 90, 'Serge Kouassi' => 80, 'Grâce Adjovi' => 70,
            'Aïssatou Diallo' => 60, 'Ousmane Sarr' => 45, 'Seydou Touré' => 40, 'Alpha Barry' => 25,
            'Mariam Traoré' => 15, 'Elom Kpodar' => 10,
            // Nouveaux membres : 0 (Mariama Ba, Brenda Tchoupo, Nadège Dossou, Rasmata Compaoré, Oumou Dembélé, Hawa Soumah).
        ];
    }
}
