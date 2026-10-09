<?php

namespace Database\Seeders;

use App\Enums\Availability;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\Concerns\Timeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Environ 40 devs fictifs de 8 pays d'Afrique francophone, plus les deux comptes de démo.
 * Les noms, villes et parcours sont inventés mais crédibles. Mot de passe : « password ».
 */
class DevSeeder extends Seeder
{
    use Timeline;

    /**
     * Membres arrivés ces tout derniers jours, sans activité (ou presque) : ils portent l'étiquette « Nouveau ».
     * Les autres se sont inscrits dans les 4 à 5 premiers jours du concours.
     */
    public const NEWCOMERS = ['Rasmata Compaoré', 'Brenda Tchoupo', 'Mariama Ba', 'Nadège Dossou', 'Oumou Dembélé', 'Hawa Soumah', 'Alpha Barry', 'Elom Kpodar'];

    public const DEMO_STARTUP_EMAIL = 'aissatou@demo.devmarche.test';

    public const DEMO_DEV_EMAIL = 'moussa@demo.devmarche.test';

    public function run(): void
    {
        $password = Hash::make('password');

        $newcomer = 0;
        foreach ($this->devs() as $index => $dev) {
            // Inscription : rien avant le lancement (29 sept.). Les comptes de démo et les membres actifs
            // arrivent dans les premiers jours ; les nouveaux venus, ces trois derniers jours.
            $joinedAt = in_array($dev['name'], self::NEWCOMERS, true)
                ? $this->clamp($this->latest()->subHours(8 + 9 * $newcomer++))
                : $this->clamp($this->launch()->addHours((int) round($index * 2.7)));

            $user = $this->travelTo($joinedAt, fn () => User::updateOrCreate(
                ['email' => $dev['email'] ?? $this->email($dev['name'])],
                ['name' => $dev['name'], 'password' => $password, 'email_verified_at' => now()],
            ));
            $user->forceFill(['created_at' => $joinedAt, 'updated_at' => $joinedAt])->save();

            $this->travelTo($joinedAt, fn () => $user->profile()->updateOrCreate([], [
                'headline' => $dev['headline'],
                'bio' => $dev['bio'],
                'country' => $dev['country'],
                'city' => $dev['city'],
                'availability' => $dev['availability'] ?? Availability::Available,
                'github_url' => ($dev['github'] ?? true) ? 'https://github.com/'.Str::slug($dev['name'], '') : null,
                'portfolio_url' => $dev['portfolio'] ?? null,
                'languages' => $dev['languages'] ?? $this->languages($dev['country'], $index),
            ]));

            $user->skills()->sync(
                collect($dev['skills'])->map(fn (string $name) => Skill::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)
            );
        }

        // Dernière activité prévue de chacun : les seeders de contenu s'y tiennent (Timeline::byActor).
        foreach ((new ActivitySeeder)->plan(User::query()->get()) as $id => $lastSeen) {
            User::query()->whereKey($id)->update(['last_seen_at' => $lastSeen]);
        }
    }

    /**
     * Langues crédibles selon le pays : le français, la langue nationale la plus parlée,
     * et l'anglais pour un dev sur trois.
     *
     * @return list<string>
     */
    private function languages(string $country, int $index): array
    {
        $local = [
            'SN' => ['wo'], 'ML' => ['bm'], 'BF' => ['mos', 'dyu'], 'CI' => ['dyu'], 'BJ' => ['fon'],
            'TG' => ['ee'], 'GN' => ['ff'], 'CM' => ['en'],
        ][$country] ?? [];

        return array_values(array_unique(array_merge(['fr'], $local, $index % 3 === 0 ? ['en'] : [])));
    }

    private function email(string $name): string
    {
        return Str::slug($name, '.').'@devmarche.test';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function devs(): array
    {
        $busy = Availability::Busy;
        $employed = Availability::Employed;

        return [
            // Comptes de démo
            [
                'email' => self::DEMO_STARTUP_EMAIL, 'name' => 'Aïssatou Diallo', 'country' => 'SN', 'city' => 'Dakar',
                'headline' => 'Fondatrice d\'une startup de paiement',
                'bio' => "Je dirige une jeune startup dakaroise qui simplifie l'encaissement pour les commerçants. Je recrute régulièrement des devs freelances pour des intégrations Mobile Money et des tableaux de bord.",
                'skills' => ['Product management', 'Figma', 'Wave API'], 'github' => false, 'availability' => $employed,
            ],
            [
                'email' => self::DEMO_DEV_EMAIL, 'name' => 'Moussa Ndiaye', 'country' => 'SN', 'city' => 'Thiès',
                'headline' => 'Dev Laravel & intégrations Mobile Money',
                'bio' => "Développeur back-end basé à Thiès. J'aime les API propres, les paiements qui ne se perdent pas et les applis qui tiennent en 3G. Disponible pour des missions à distance.",
                'skills' => ['Laravel', 'PHP', 'Livewire', 'PostgreSQL', 'Wave API', 'Orange Money'],
            ],

            // Sénégal
            ['name' => 'Fatou Sow', 'country' => 'SN', 'city' => 'Dakar', 'headline' => 'Dev Flutter', 'skills' => ['Flutter', 'Dart', 'Firebase', 'Orange Money'],
                'bio' => 'Je construis des applis mobiles pour les PME sénégalaises : livraison, prise de rendez-vous, fidélité. Trois applis en production sur le Play Store.'],
            ['name' => 'Cheikh Fall', 'country' => 'SN', 'city' => 'Dakar', 'headline' => 'DevOps & cloud', 'skills' => ['Docker', 'Linux', 'DevOps', 'PostgreSQL', 'Nginx'], 'availability' => $employed,
                'bio' => 'Ingénieur DevOps dans une fintech de Dakar. Je donne volontiers un coup de main sur les déploiements, la supervision et les sauvegardes.'],
            ['name' => 'Mariama Ba', 'country' => 'SN', 'city' => 'Saint-Louis', 'headline' => 'Étudiante en génie logiciel', 'skills' => ['Python', 'Django', 'React'],
                'bio' => 'Étudiante à Saint-Louis, je cherche une première mission pour mettre en pratique Django et React. Curieuse et rigoureuse.'],
            ['name' => 'Ibrahima Diop', 'country' => 'SN', 'city' => 'Ziguinchor', 'headline' => 'Dev full-stack JavaScript', 'skills' => ['Node.js', 'React', 'TypeScript', 'MongoDB'], 'availability' => $busy,
                'bio' => "Full-stack JavaScript depuis cinq ans. J'ai travaillé sur des plateformes e-commerce et des outils internes pour des ONG en Casamance."],
            ['name' => 'Awa Seck', 'country' => 'SN', 'city' => 'Dakar', 'headline' => 'Designer UI/UX', 'skills' => ['Figma', 'UI/UX', 'Tailwind CSS'],
                'bio' => "Designer d'interfaces pour le mobile africain : écrans lisibles au soleil, parcours courts, peu de données consommées.", 'portfolio' => 'https://awaseck.design'],
            ['name' => 'Ousmane Sarr', 'languages' => ['fr', 'wo', 'ff'], 'country' => 'SN', 'city' => 'Thiès', 'headline' => 'Dev Odoo', 'skills' => ['Odoo', 'Python', 'PostgreSQL'],
                'bio' => "J'installe et je personnalise Odoo pour les commerces et les cabinets : facturation en FCFA, stocks, paie."],

            // Côte d'Ivoire
            ['name' => 'Kouadio Yao', 'country' => 'CI', 'city' => 'Abidjan', 'headline' => 'Dev back-end Java', 'skills' => ['Java', 'Spring Boot', 'PostgreSQL', 'MTN MoMo API'], 'availability' => $employed,
                'bio' => 'Développeur Java dans une banque à Abidjan. Spécialiste des intégrations MTN MoMo et des API de rapprochement bancaire.'],
            ['name' => 'Aya Koné', 'country' => 'CI', 'city' => 'Abidjan', 'headline' => 'Dev React Native', 'skills' => ['React Native', 'TypeScript', 'Firebase'],
                'bio' => "Je développe des applis mobiles multiplateformes, du prototype à la publication. Passionnée par l'accessibilité."],
            ['name' => 'Serge Kouassi', 'country' => 'CI', 'city' => 'Bouaké', 'headline' => 'Dev PHP & WordPress', 'skills' => ['PHP', 'WordPress', 'MySQL', 'Laravel'],
                'bio' => "Sites vitrines, boutiques en ligne et sites d'associations depuis Bouaké. Je forme aussi des jeunes au développement web."],
            ['name' => 'Mariam Traoré', 'country' => 'CI', 'city' => 'Yamoussoukro', 'headline' => 'Data analyst', 'skills' => ['Python', 'Power BI', 'Data Science', 'PostgreSQL'],
                'bio' => "J'aide les coopératives agricoles à suivre leurs ventes et leurs stocks avec des tableaux de bord simples."],
            ['name' => 'Jean-Marc N\'Guessan', 'country' => 'CI', 'city' => 'Abidjan', 'headline' => 'Architecte logiciel', 'skills' => ['Go', 'Docker', 'Kubernetes', 'PostgreSQL'], 'availability' => $busy,
                'bio' => "Quinze ans d'expérience, dont dix dans les télécoms. Je conseille les startups sur l'architecture et la montée en charge."],

            // Cameroun
            ['name' => 'Arnaud Mbarga', 'country' => 'CM', 'city' => 'Yaoundé', 'headline' => 'Dev Django', 'skills' => ['Django', 'Python', 'PostgreSQL', 'Docker'],
                'bio' => "Back-end Django pour des plateformes éducatives. J'aime écrire des tests et des API bien documentées."],
            ['name' => 'Christelle Ngo Biyong', 'country' => 'CM', 'city' => 'Douala', 'headline' => 'Dev Flutter & Firebase', 'skills' => ['Flutter', 'Dart', 'Firebase', 'Supabase'],
                'bio' => 'Applis mobiles pour la logistique portuaire et la livraison à Douala. Je travaille en français et en anglais.'],
            ['name' => 'Paul Fotso', 'country' => 'CM', 'city' => 'Douala', 'headline' => 'Dev full-stack Laravel & Vue', 'skills' => ['Laravel', 'Vue.js', 'MySQL', 'Tailwind CSS'], 'availability' => $busy,
                'bio' => 'Je livre des applications de gestion complètes : stocks, caisse, facturation. Une quinzaine de clients au Cameroun et au Gabon.'],
            ['name' => 'Brenda Tchoupo', 'country' => 'CM', 'city' => 'Bafoussam', 'headline' => 'Dev front-end', 'skills' => ['React', 'Next.js', 'TypeScript', 'Tailwind CSS'],
                'bio' => 'Développeuse front-end autodidacte, je viens de terminer un bootcamp. Je cherche des projets pour progresser.'],
            ['name' => 'Samuel Nkwenti', 'country' => 'CM', 'city' => 'Buea', 'headline' => 'Ingénieur mobile Kotlin', 'skills' => ['Kotlin', 'Android', 'Firebase', 'USSD'],
                'bio' => 'Android natif et services USSD pour les zones à faible connectivité. Basé dans la Silicon Mountain de Buea.'],

            // Bénin
            ['name' => 'Rodrigue Hounsou', 'country' => 'BJ', 'city' => 'Cotonou', 'headline' => 'Dev Laravel', 'skills' => ['Laravel', 'PHP', 'MySQL', 'MTN MoMo API'],
                'bio' => "Laravel depuis la version 5. J'ai intégré MTN MoMo et Moov Money pour plusieurs e-commerçants de Cotonou."],
            ['name' => 'Grâce Adjovi', 'country' => 'BJ', 'city' => 'Cotonou', 'headline' => 'Product designer', 'skills' => ['Figma', 'UI/UX', 'Product management'],
                'bio' => 'Je conçois des parcours simples pour des utilisateurs qui découvrent le numérique. Ateliers utilisateurs sur le terrain.'],
            ['name' => 'Fiacre Agossou', 'languages' => ['fr', 'fon', 'yo', 'en'], 'country' => 'BJ', 'city' => 'Porto-Novo', 'headline' => 'Dev Python & IA', 'skills' => ['Python', 'FastAPI', 'Data Science'], 'availability' => $employed,
                'bio' => 'Je travaille sur la reconnaissance vocale en fon et en yoruba. Toujours partant pour échanger sur le traitement des langues africaines.'],
            ['name' => 'Nadège Dossou', 'country' => 'BJ', 'city' => 'Parakou', 'headline' => 'Dev web junior', 'skills' => ['HTML', 'CSS', 'JavaScript', 'WordPress'], 'github' => false,
                'bio' => 'Jeune développeuse à Parakou, je réalise des sites pour les commerces du quartier. Je veux apprendre Laravel.'],
            ['name' => 'Mathieu Ahouansou', 'country' => 'BJ', 'city' => 'Cotonou', 'headline' => 'Dev Symfony', 'skills' => ['Symfony', 'PHP', 'PostgreSQL', 'Docker'], 'availability' => $busy,
                'bio' => 'Back-end Symfony pour des administrations et des ONG. Attaché à la sécurité et à la qualité du code.'],

            // Togo
            ['name' => 'Kossi Mensah', 'country' => 'TG', 'city' => 'Lomé', 'headline' => 'Dev full-stack Node', 'skills' => ['Node.js', 'React', 'PostgreSQL', 'Docker'],
                'bio' => "Je développe des plateformes de réservation et de billetterie. J'organise aussi des meetups de devs à Lomé."],
            ['name' => 'Akossiwa Agbeko', 'country' => 'TG', 'city' => 'Lomé', 'headline' => 'Dev mobile Flutter', 'skills' => ['Flutter', 'Dart', 'Supabase', 'Orange Money'],
                'bio' => 'Applis mobiles pour la santé communautaire : rappels de vaccins, suivi des consultations, mode hors ligne.'],
            ['name' => 'Yawo Amegbor', 'country' => 'TG', 'city' => 'Kara', 'headline' => 'Administrateur systèmes', 'skills' => ['Linux', 'Nginx', 'DevOps', 'MySQL'], 'availability' => $employed,
                'bio' => "Administrateur systèmes et réseaux. J'aide à héberger des applications à moindre coût, au plus près des utilisateurs."],
            ['name' => 'Elom Kpodar', 'country' => 'TG', 'city' => 'Lomé', 'headline' => 'Dev Django & React', 'skills' => ['Django', 'React', 'PostgreSQL'],
                'bio' => "Développeur full-stack, j'ai travaillé sur des outils de microfinance. Je cherche des missions longues à distance."],
            ['name' => 'Edem Afenyo', 'country' => 'TG', 'city' => 'Lomé', 'headline' => 'Dev back-end PHP', 'skills' => ['PHP', 'Laravel', 'PayDunya', 'MySQL'],
                'bio' => 'Intégrations de paiement (PayDunya, Mobile Money) et API pour des boutiques en ligne au Togo et au Bénin.'],

            // Burkina Faso
            ['name' => 'Boukary Ouédraogo', 'country' => 'BF', 'city' => 'Ouagadougou', 'headline' => 'Dev Laravel & Vue', 'skills' => ['Laravel', 'Vue.js', 'PostgreSQL', 'Orange Money'],
                'bio' => 'Je développe des logiciels de gestion pour les écoles et les centres de santé à Ouagadougou.'],
            ['name' => 'Salimata Sawadogo', 'country' => 'BF', 'city' => 'Bobo-Dioulasso', 'headline' => 'Data scientist', 'skills' => ['Python', 'Data Science', 'Power BI'], 'availability' => $busy,
                'bio' => "Analyse de données agricoles et climatiques. J'aime rendre les chiffres compréhensibles pour tous."],
            ['name' => 'Abdoul Kaboré', 'country' => 'BF', 'city' => 'Ouagadougou', 'headline' => 'Dev Android', 'skills' => ['Kotlin', 'Java', 'Android', 'USSD'],
                'bio' => "Applications Android et services USSD pour l'inclusion financière. Six ans d'expérience."],
            ['name' => 'Rasmata Compaoré', 'country' => 'BF', 'city' => 'Ouagadougou', 'headline' => 'Étudiante en informatique', 'skills' => ['JavaScript', 'React', 'Node.js'], 'github' => false,
                'bio' => "Étudiante en licence, je découvre React et Node.js. Je pose beaucoup de questions et j'aime aider en retour."],

            // Mali
            ['name' => 'Bakary Coulibaly', 'country' => 'ML', 'city' => 'Bamako', 'headline' => 'Dev full-stack', 'skills' => ['Laravel', 'React', 'MySQL', 'Orange Money'],
                'bio' => 'Full-stack à Bamako : plateformes de commande, gestion de flotte, intégration Orange Money Mali.'],
            ['name' => 'Kadiatou Keïta', 'country' => 'ML', 'city' => 'Bamako', 'headline' => 'Dev Python', 'skills' => ['Python', 'Django', 'FastAPI', 'PostgreSQL'], 'availability' => $employed,
                'bio' => 'Développeuse back-end dans une ONG, je construis des outils de collecte de données sur le terrain.'],
            ['name' => 'Seydou Touré', 'country' => 'ML', 'city' => 'Ségou', 'headline' => 'Dev web freelance', 'skills' => ['WordPress', 'PHP', 'JavaScript'],
                'bio' => 'Sites web pour les artisans et les hôtels de Ségou, avec paiement en ligne et réservation.'],
            ['name' => 'Oumou Dembélé', 'country' => 'ML', 'city' => 'Bamako', 'headline' => 'Dev mobile', 'skills' => ['Flutter', 'Dart', 'Firebase'],
                'bio' => 'Applis mobiles en bambara et en français pour rendre les services numériques accessibles à tous.'],

            // Guinée
            ['name' => 'Mamadou Bah', 'languages' => ['fr', 'ff', 'en'], 'country' => 'GN', 'city' => 'Conakry', 'headline' => 'Dev back-end Node', 'skills' => ['Node.js', 'TypeScript', 'MongoDB', 'Orange Money'],
                'bio' => "API et micro-services pour une startup de livraison à Conakry. Je m'intéresse beaucoup à la fiabilité des paiements."],
            ['name' => 'Fatoumata Camara', 'country' => 'GN', 'city' => 'Conakry', 'headline' => 'Dev full-stack Laravel', 'skills' => ['Laravel', 'Livewire', 'Tailwind CSS', 'MySQL'], 'availability' => $busy,
                'bio' => 'Laravel et Livewire pour des applications de gestion rapides à livrer. Mentor pour les femmes qui débutent dans le code.'],
            ['name' => 'Alpha Barry', 'country' => 'GN', 'city' => 'Kankan', 'headline' => 'Dev web', 'skills' => ['PHP', 'MySQL', 'JavaScript'],
                'bio' => 'Développeur web à Kankan, je numérise la gestion des écoles et des commerces de la région.'],
            ['name' => 'Hawa Soumah', 'country' => 'GN', 'city' => 'Labé', 'headline' => 'Designer & dev front-end', 'skills' => ['Figma', 'React', 'Tailwind CSS'],
                'bio' => 'Je fais le lien entre le design et le code : maquettes Figma et intégration React soignée.'],
        ];
    }
}
