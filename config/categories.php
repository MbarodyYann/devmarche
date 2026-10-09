<?php

/*
| Catégories populaires de l'accueil (idée reprise de Fiverr) : chaque catégorie regroupe des compétences
| et mène à /missions?categorie=<clé>. Les noms de compétences sont ceux de la table skills.
| Icônes : Lucide (composant x-icon).
*/

return [
    'paiement-mobile' => [
        'label' => 'Paiement mobile',
        'icon' => 'wallet',
        'skills' => ['Wave API', 'Orange Money', 'MTN MoMo API', 'PayDunya'],
    ],
    'sites-web' => [
        'label' => 'Sites web',
        'icon' => 'globe',
        'skills' => ['Laravel', 'PHP', 'Symfony', 'Livewire', 'WordPress', 'Django', 'Node.js', 'Next.js', 'React', 'Vue.js', 'JavaScript', 'TypeScript', 'HTML', 'CSS', 'Tailwind CSS'],
    ],
    'applis-mobiles' => [
        'label' => 'Applis mobiles',
        'icon' => 'smartphone',
        'skills' => ['Flutter', 'Dart', 'React Native', 'Kotlin', 'Android', 'Firebase'],
    ],
    'ussd-sms' => [
        'label' => 'USSD et SMS',
        'icon' => 'hash',
        'skills' => ['USSD'],
    ],
    'data-ia' => [
        'label' => 'Data et IA',
        'icon' => 'chart',
        'skills' => ['Python', 'Data Science', 'Power BI', 'FastAPI'],
    ],
    'design-ui' => [
        'label' => 'Design UI',
        'icon' => 'pen-tool',
        'skills' => ['Figma', 'UI/UX'],
    ],
];
