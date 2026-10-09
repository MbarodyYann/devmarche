<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Mission;
use App\Models\User;
use App\Services\MissionService;
use Database\Seeders\Concerns\Timeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 25 missions crédibles, dans tous les statuts, avec leurs candidatures.
 * Les missions terminées créditent les freelances via ReputationService (événements mission_completed).
 * Le reste de la réputation vient des questions-réponses (QuestionSeeder).
 *
 * Pour le scénario de démo, la mission Wave d'Aïssatou reste OUVERTE.
 */
class MissionSeeder extends Seeder
{
    use Timeline;

    public function __construct(
        private readonly MissionService $missions,
    ) {}

    public function run(): void
    {
        if (Mission::query()->exists()) {
            return; // déjà semé : on ne duplique pas
        }

        $users = User::query()->get()->keyBy('name');
        $user = fn (string $name): User => $users[$name] ?? throw new \RuntimeException("Dev inconnu dans les seeders : $name");

        DB::transaction(function () use ($user) {
            foreach (self::missions() as $data) {
                $client = $user($data['client']);

                // Publication : plus la mission est « ancienne », plus elle est proche du lancement,
                // et jamais avant l'inscription du client.
                $createdAt = $this->byActor($client, $this->missionAt($data['days'], $client->created_at), $client->created_at->copy()->addHours(3));

                $mission = $this->travelTo($createdAt, fn () => $this->missions->publish($client, [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'budget_fcfa' => $data['budget'],
                    'country' => $data['country'],
                    'deadline' => isset($data['deadline']) ? now()->addDays($data['deadline'])->toDateString() : null,
                ], $data['skills']));

                // Candidatures : après la publication et après l'inscription du candidat.
                $applications = [];
                $lastApplicationAt = $createdAt;
                foreach ($data['applicants'] ?? [] as $i => $name) {
                    $applicant = $user($name);
                    if (! $this->requiredApplication($data, $name) && $this->goneBefore($applicant, $createdAt->copy()->addMinutes(20))) {
                        continue; // parti avant la publication
                    }
                    $appliedAt = $this->byActor(
                        $applicant,
                        $this->applicationAt($createdAt, $i, $applicant->created_at),
                        $createdAt->copy()->addMinutes(20)->max($applicant->created_at->copy()->addHour()),
                    );
                    $lastApplicationAt = $lastApplicationAt->max($appliedAt);

                    $applications[$name] = $this->travelTo($appliedAt, function () use ($mission, $data, $i, $applicant) {
                        $application = $mission->applications()->make([
                            'message' => $this->message($data, $i),
                            'proposed_fcfa' => (int) round($data['budget'] * [1, 0.9, 1.1, 0.95, 1.05][$i % 5] / 1000) * 1000,
                            'delivery_days' => $this->deliveryDays($data['budget'], $i),
                        ]);
                        $application->user()->associate($applicant);
                        $application->save();

                        return $application;
                    });
                }

                // Attribution, puis fin de mission : toujours après les candidatures, jamais dans le futur.
                $assignedAt = $this->between($lastApplicationAt, 0.3);
                match ($data['status']) {
                    'assigned' => $this->travelTo($assignedAt, fn () => $this->missions->accept($mission, $applications[$data['freelancer']])),
                    'completed' => $this->complete($mission, $applications[$data['freelancer']], $assignedAt),
                    'cancelled' => $this->travelTo($this->between($lastApplicationAt, 0.4), fn () => $this->missions->cancel($mission)),
                    default => null,
                };
            }
        });
    }

    private function complete(Mission $mission, Application $application, Carbon $assignedAt): void
    {
        $this->travelTo($assignedAt, fn () => $this->missions->accept($mission, $application));
        $this->travelTo($this->between($assignedAt, 0.6), fn () => $this->missions->complete($mission->refresh()));
    }

    /** Délai proposé crédible, selon l'ampleur de la mission. */
    private function deliveryDays(int $budget, int $i): int
    {
        $base = match (true) {
            $budget <= 100_000 => 5,
            $budget <= 250_000 => 12,
            $budget <= 400_000 => 21,
            default => 30,
        };

        return max(2, $base + [0, -2, 3, 5, -3][$i % 5]);
    }

    private function message(array $data, int $i): string
    {
        // Messages écrits à la main, variés (approche, réalisation, question, disponibilité), choisis de façon
        // stable selon la mission et le rang du candidat.
        $messages = [
            "Bonjour, j'ai livré un projet très proche l'an dernier. Je peux vous montrer le dépôt pendant un appel.",
            "Bonjour ! Je propose de commencer par une maquette cliquable en deux jours, pour valider le parcours avant d'écrire la moindre ligne.",
            "Bonsoir, une question avant de chiffrer définitivement : vous avez déjà un compte marchand, ou il faut aussi s'occuper de l'ouverture ?",
            'Disponible dès lundi, à temps plein pendant trois semaines. Je travaille en livraisons hebdomadaires, avec une démo chaque vendredi.',
            'Salam, je connais bien les coupures réseau côté utilisateurs : je prévois des reprises automatiques et des messages clairs quand la connexion tombe.',
            'Bonjour, si nous sommes dans la même ville, je peux passer dans vos locaux. Mon dernier projet du même type est en ligne, je vous envoie le lien.',
            "J'ai lu votre description deux fois : le point délicat, c'est la synchronisation. Je propose de le traiter en premier pour éviter les surprises.",
            "Bonjour, je préfère être transparent : je suis aussi sur une autre mission jusqu'au 15, donc à mi-temps la première semaine, puis à temps plein.",
            'Je peux livrer une première version utilisable en dix jours, puis on ajuste ensemble. Les tests automatiques sont inclus dans mon prix.',
            "Bonjour ! Vous avez un exemple de site ou d'appli dont vous aimez le style ? Ça m'aide à viser juste dès la première maquette.",
            'Développeur depuis six ans, dont trois en freelance. Mes clients me rappellent surtout pour la maintenance, je reste disponible après la livraison.',
            'Bonjour, je propose un appel de 20 minutes cette semaine pour cadrer les priorités. Je suis joignable sur WhatsApp en fin de journée.',
            'Je viens de terminer un travail similaire pour une coopérative. Je connais les pièges de la mise en production.',
            'Bonsoir, prix ferme, sans supplément caché. Je documente tout pour que votre équipe puisse reprendre le code sans moi.',
            'Bonjour, je peux aussi former une personne de votre équipe pendant la dernière semaine, si ça vous intéresse.',
            "Mission qui me plaît beaucoup. Je commencerais par un audit rapide de l'existant, puis je vous envoie un plan en trois étapes.",
        ];

        return $messages[(crc32($data['title']) + $i * 7) % count($messages)];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function missions(): array
    {
        return [
            // La mission de la démo : ouverte, publiée par Aïssatou.
            [
                'client' => 'Aïssatou Diallo', 'status' => 'open', 'days' => 1, 'deadline' => 21, 'demo' => true,
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
            // Missions terminées des devs les plus expérimentés (niveaux Expert et Référence).
            [
                'client' => 'Cheikh Fall', 'status' => 'completed', 'freelancer' => 'Jean-Marc N\'Guessan', 'days' => 88,
                'title' => 'Revue d\'architecture d\'une API de transfert d\'argent',
                'budget' => 650000, 'country' => null, 'skills' => ['Go', 'PostgreSQL', 'DevOps'],
                'description' => "Revue complète de l'architecture avant une levée de fonds : points de défaillance, montée en charge, sauvegardes et sécurité. Rapport priorisé et atelier de restitution avec l'équipe.",
                'applicants' => ['Jean-Marc N\'Guessan', 'Kouadio Yao'],
            ],
            [
                'client' => 'Aya Koné', 'status' => 'completed', 'freelancer' => 'Jean-Marc N\'Guessan', 'days' => 85,
                'title' => 'Mise en place de l\'intégration continue pour une appli mobile',
                'budget' => 280000, 'country' => 'CI', 'skills' => ['DevOps', 'Docker'],
                'description' => 'Tests automatiques à chaque envoi de code, construction des versions de test et publication sur le store en un clic.',
                'applicants' => ['Jean-Marc N\'Guessan', 'Yawo Amegbor'],
            ],
            [
                'client' => 'Kossi Mensah', 'status' => 'completed', 'freelancer' => 'Jean-Marc N\'Guessan', 'days' => 80,
                'title' => 'Montée en charge d\'une billetterie avant un grand concert',
                'budget' => 450000, 'country' => 'TG', 'skills' => ['Node.js', 'PostgreSQL', 'Docker'],
                'description' => "Préparer la plateforme à 20 000 connexions simultanées le jour de l'ouverture des ventes : tests de charge, cache, file d'attente virtuelle.",
                'applicants' => ['Jean-Marc N\'Guessan'],
            ],
            [
                'client' => 'Mathieu Ahouansou', 'status' => 'completed', 'freelancer' => 'Jean-Marc N\'Guessan', 'days' => 75,
                'title' => 'Audit de sécurité d\'un back-office d\'ONG',
                'budget' => 320000, 'country' => 'BJ', 'skills' => ['Symfony', 'DevOps'],
                'description' => "Revue des droits d'accès, des mots de passe, des sauvegardes et des dépendances. Correctifs prioritaires appliqués avec l'équipe.",
                'applicants' => ['Jean-Marc N\'Guessan', 'Rodrigue Hounsou'],
            ],
            [
                'client' => 'Arnaud Mbarga', 'status' => 'completed', 'freelancer' => 'Jean-Marc N\'Guessan', 'days' => 70,
                'title' => 'Migration d\'une base MySQL vers PostgreSQL sans interruption',
                'budget' => 380000, 'country' => null, 'skills' => ['PostgreSQL', 'Docker'],
                'description' => "Migration d'une base de 40 Go vers PostgreSQL, avec réplication et bascule de nuit en moins de 10 minutes d'interruption.",
                'applicants' => ['Jean-Marc N\'Guessan'],
            ],
            [
                'client' => 'Paul Fotso', 'status' => 'completed', 'freelancer' => 'Kouadio Yao', 'days' => 86,
                'title' => 'Paiement MTN MoMo sur une plateforme de caisse',
                'budget' => 240000, 'country' => 'CM', 'skills' => ['MTN MoMo API', 'Java'],
                'description' => "Encaissement MTN MoMo en caisse, avec confirmation sur l'écran du vendeur et rapprochement automatique en fin de journée.",
                'applicants' => ['Kouadio Yao', 'Rodrigue Hounsou'],
            ],
            [
                'client' => 'Bakary Coulibaly', 'status' => 'completed', 'freelancer' => 'Kouadio Yao', 'days' => 78,
                'title' => 'API de rapprochement bancaire pour une microfinance',
                'budget' => 420000, 'country' => 'ML', 'skills' => ['Java', 'Spring Boot', 'PostgreSQL'],
                'description' => 'Rapprocher chaque jour les relevés bancaires et les remboursements Mobile Money, avec la liste des écarts à traiter.',
                'applicants' => ['Kouadio Yao'],
            ],
            [
                'client' => 'Salimata Sawadogo', 'status' => 'completed', 'freelancer' => 'Kouadio Yao', 'days' => 72,
                'title' => 'Encaissement des cotisations d\'une coopérative par Mobile Money',
                'budget' => 260000, 'country' => 'BF', 'skills' => ['Orange Money', 'Java'],
                'description' => 'Les membres paient leur cotisation par Orange Money ; la coopérative voit en temps réel qui est à jour.',
                'applicants' => ['Kouadio Yao', 'Boukary Ouédraogo'],
            ],
            [
                'client' => 'Christelle Ngo Biyong', 'status' => 'open', 'days' => 13,
                'title' => 'Tests automatisés pour une appli Flutter',
                'budget' => 140000, 'country' => null, 'skills' => ['Flutter', 'Dart'],
                'description' => "Écrire les tests widget et d'intégration des parcours critiques (inscription, paiement) et les brancher dans notre intégration continue.",
                'applicants' => ['Fatou Sow'],
            ],
            [
                'client' => 'Salimata Sawadogo', 'status' => 'open', 'days' => 6, 'deadline' => 25,
                'title' => 'Menu USSD pour consulter son solde de cotisations',
                'budget' => 220000, 'country' => 'BF', 'skills' => ['USSD', 'Node.js'],
                'description' => "Les membres de la coopérative n'ont pas tous un smartphone. Nous voulons un menu USSD (*123#) pour consulter son solde, ses dernières cotisations et recevoir un SMS de confirmation. L'agrégateur USSD est déjà choisi.",
                'applicants' => ['Abdoul Kaboré', 'Boukary Ouédraogo'],
            ],
        ];
    }
}
