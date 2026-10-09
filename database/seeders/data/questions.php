<?php

/*
| Questions-réponses de démonstration : problèmes réels des devs d'Afrique francophone.
| Chaque question : auteur, titre, tags, corps (Markdown), ancienneté en jours, votes sur la question,
| et ses réponses (auteur, corps, acceptée ou non). Les votes sur les réponses sont calculés par
| QuestionSeeder pour donner à chacun une réputation variée et cohérente.
*/

return [
    [
        'author' => 'Moussa Ndiaye', 'days' => 40, 'votes' => 9, 'tags' => ['Wave API', 'Laravel', 'PHP'],
        'title' => 'Comment vérifier la signature d\'un webhook Wave en Laravel ?',
        'body' => "Je reçois bien les webhooks Wave sur `/webhooks/wave`, mais je ne sais pas comment m'assurer qu'ils viennent vraiment de Wave. La doc parle d'un en-tête `Wave-Signature`.\n\nComment le vérifier proprement dans un contrôleur Laravel ?",
        'answers' => [
            ['author' => 'Rodrigue Hounsou', 'accepted' => true, 'body' => "L'en-tête contient un horodatage et une signature HMAC-SHA256 du corps brut, calculée avec votre secret de webhook. Il faut **le corps brut**, pas le JSON décodé :\n\n```php\n\$header = \$request->header('Wave-Signature'); // t=1696…,v1=abc…\nparse_str(str_replace(',', '&', \$header), \$parts);\n\n\$payload = \$parts['t'].\$request->getContent();\n\$expected = hash_hmac('sha256', \$payload, config('services.wave.webhook_secret'));\n\nabort_unless(hash_equals(\$expected, \$parts['v1'] ?? ''), 401);\nabort_if(abs(time() - (int) \$parts['t']) > 300, 401); // rejeu\n```\n\nUtilisez toujours `hash_equals` (comparaison à temps constant) et refusez les horodatages de plus de 5 minutes."],
            ['author' => 'Edem Afenyo', 'body' => "En complément : rendez le traitement **idempotent**. Wave peut renvoyer le même événement plusieurs fois. Je stocke l'`id` de l'événement dans une table avec un index unique, et je l'ignore s'il existe déjà."],
        ],
    ],
    [
        'author' => 'Brenda Tchoupo', 'days' => 3, 'votes' => 4, 'tags' => ['Orange Money', 'Node.js'],
        'title' => 'Le callback Orange Money n\'arrive jamais sur mon serveur local',
        'body' => "Je teste l'API Orange Money Web Payment. Le paiement passe, mais la `notif_url` n'est jamais appelée. Mon serveur tourne sur `localhost:3000`.\n\nEst-ce normal ?",
        'answers' => [
            ['author' => 'Mamadou Bah', 'accepted' => true, 'body' => "Oui : Orange ne peut pas joindre `localhost`. Il faut une URL publique en HTTPS. En développement, exposez votre port avec un tunnel (ngrok, Cloudflare Tunnel…) et mettez cette URL dans `notif_url`.\n\nVérifiez aussi que vous répondez `200` rapidement : si le traitement est long, mettez-le dans une file et répondez tout de suite."],
            ['author' => 'Bakary Coulibaly', 'body' => "Attention aussi au pays : l'environnement de test n'est pas activé partout de la même façon. Au Mali, j'ai dû demander l'activation du *callback* au support marchand."],
        ],
    ],
    [
        'author' => 'Rasmata Compaoré', 'days' => 1, 'votes' => 3, 'tags' => ['USSD', 'Node.js'],
        'title' => 'Comment garder l\'état d\'une session USSD entre deux écrans ?',
        'body' => "Je construis un menu USSD (`*123#`). À chaque réponse de l'utilisateur, l'agrégateur appelle mon API avec le texte complet saisi. Comment savoir à quel écran il en est ?",
        'answers' => [
            ['author' => 'Samuel Nkwenti', 'accepted' => true, 'body' => "La plupart des agrégateurs envoient un `sessionId` et le texte cumulé, par exemple `1*2*500`. Deux approches :\n\n1. **Sans état** : découpez `text` sur `*` et déduisez l'écran de la longueur du tableau.\n2. **Avec état** : stockez la session dans Redis avec une durée de vie de 3 minutes, clé = `sessionId`.\n\n```js\nconst steps = text === '' ? [] : text.split('*');\nif (steps.length === 0) return 'CON 1. Solde\\n2. Historique';\nif (steps[0] === '1') return 'END Votre solde : 12 500 FCFA';\n```\n\nCommencez par `CON` pour continuer la session, `END` pour la terminer."],
            ['author' => 'Abdoul Kaboré', 'body' => "Gardez les écrans très courts : 182 caractères maximum chez beaucoup d'opérateurs, et pas d'accents sur certains anciens téléphones."],
        ],
    ],
    [
        'author' => 'Mariama Ba', 'days' => 5, 'votes' => 7, 'tags' => ['Laravel', 'Performance'],
        'title' => 'Mon appli Laravel est très lente pour mes utilisateurs en 3G',
        'body' => 'En local tout va vite, mais mes utilisateurs à Saint-Louis attendent 8 à 10 secondes. Par où commencer ?',
        'answers' => [
            ['author' => 'Jean-Marc N\'Guessan', 'accepted' => true, 'body' => "Mesurez d'abord le **poids** de la page, pas le temps serveur. En 3G, chaque Ko compte :\n\n- compressez (gzip ou brotli) au niveau de Nginx ;\n- servez les images en WebP, à la bonne taille, avec `loading=\"lazy\"` sous la ligne de flottaison ;\n- auto-hébergez les polices et limitez-vous à 2 fichiers ;\n- évitez les gros bundles JS : du rendu serveur avec un peu d'Alpine suffit souvent ;\n- mettez en cache la config et les routes en production (`php artisan optimize`).\n\nVisez moins de 200 Ko au premier chargement. Testez avec le profil « Slow 3G » des outils de Chrome."],
            ['author' => 'Cheikh Fall', 'body' => "Côté serveur, activez OPcache et vérifiez les requêtes N+1 avec `Model::preventLazyLoading()` en développement. Une liste de 50 éléments avec 2 relations non chargées, c'est 101 requêtes."],
            ['author' => 'Fatoumata Camara', 'body' => "Et hébergez au plus près de vos utilisateurs : un serveur à Dakar plutôt qu'en Europe, c'est souvent 100 à 150 ms de gagnés par aller-retour."],
        ],
    ],
    [
        'author' => 'Nadège Dossou', 'days' => 6, 'votes' => 5, 'tags' => ['Performance', 'WordPress'],
        'title' => 'Comment optimiser les images d\'un site pour le mobile ?',
        'body' => "Mes clients m'envoient des photos de 4 Mo prises au téléphone. Comment les rendre légères sans perdre trop en qualité ?",
        'answers' => [
            ['author' => 'Awa Seck', 'accepted' => true, 'body' => "Trois règles simples :\n\n1. **Redimensionnez** à la taille d'affichage réelle (rarement plus de 1 200 px de large).\n2. **Convertissez en WebP**, qualité 70 à 80 : souvent 10 fois plus léger qu'un JPEG brut.\n3. **Proposez plusieurs tailles** avec `srcset` et `sizes`, pour que le téléphone télécharge la petite.\n\n```html\n<img src=\"plat-800.webp\" srcset=\"plat-480.webp 480w, plat-800.webp 800w\"\n     sizes=\"(min-width: 768px) 50vw, 100vw\" width=\"800\" height=\"600\" alt=\"Thieboudienne\" loading=\"lazy\">\n```\n\nToujours `width` et `height`, pour éviter que la page saute pendant le chargement."],
            ['author' => 'Serge Kouassi', 'body' => "Sur WordPress, une extension de conversion WebP à l'envoi fait le travail automatiquement. Pensez à régénérer les miniatures existantes."],
        ],
    ],
    [
        'author' => 'Elom Kpodar', 'days' => 12, 'votes' => 6, 'tags' => ['DevOps', 'Linux', 'Laravel'],
        'title' => 'Déployer une appli Laravel sur un VPS à Dakar : quelle configuration minimale ?',
        'body' => "Je dois déployer une petite appli Laravel (PostgreSQL) sur un VPS de 2 vCPU et 4 Go. Quelle pile installer, et qu'est-ce qu'on oublie souvent ?",
        'answers' => [
            ['author' => 'Yawo Amegbor', 'accepted' => true, 'body' => "Pile classique et légère : Ubuntu LTS, Nginx, PHP-FPM, PostgreSQL, Composer, et Supervisor pour les files d'attente.\n\nCe qu'on oublie souvent :\n\n- le **pare-feu** (`ufw allow OpenSSH`, puis 80 et 443 seulement) ;\n- les **sauvegardes** de la base, testées, hors du serveur ;\n- le **HTTPS** avec Let's Encrypt et son renouvellement automatique ;\n- `APP_DEBUG=false` en production ;\n- les droits sur `storage/` et `bootstrap/cache/`.\n\n```bash\nphp artisan migrate --force\nphp artisan optimize\nsudo systemctl reload php8.2-fpm\n```"],
            ['author' => 'Cheikh Fall', 'body' => 'Ajoutez une supervision minimale (disque, mémoire, certificat qui expire). Un disque plein à cause des logs est la panne la plus fréquente que je vois.'],
        ],
    ],
    [
        'author' => 'Oumou Dembélé', 'days' => 9, 'votes' => 4, 'tags' => ['Flutter', 'Dart'],
        'title' => 'Flutter : comment faire fonctionner mon appli hors connexion ?',
        'body' => "Mes utilisateurs perdent souvent le réseau. Je voudrais qu'ils puissent saisir des données hors ligne et que tout se synchronise ensuite.",
        'answers' => [
            ['author' => 'Christelle Ngo Biyong', 'accepted' => true, 'body' => "Le principe : **la base locale est la source de vérité**, le serveur est synchronisé ensuite.\n\n1. Stockez tout localement (sqflite, Drift ou Isar).\n2. Ajoutez à chaque ligne un champ `synced` (booléen) et un `updated_at`.\n3. Une file d'envoi tente de pousser les lignes non synchronisées quand la connexion revient (`connectivity_plus`).\n4. Côté serveur, rendez l'enregistrement idempotent (identifiant UUID généré par le téléphone)."],
            ['author' => 'Akossiwa Agbeko', 'body' => "Prévoyez les conflits : si deux appareils modifient la même ligne, décidez d'une règle simple (« le dernier qui écrit gagne ») et affichez-la à l'utilisateur."],
            ['author' => 'Fatou Sow', 'body' => "Affichez clairement l'état « non envoyé » sur chaque élément. Les utilisateurs sont rassurés quand ils voient que rien n'est perdu."],
        ],
    ],
    [
        'author' => 'Hawa Soumah', 'days' => 7, 'votes' => 2, 'tags' => ['React', 'Tailwind CSS'],
        'title' => 'Tailwind : mon menu mobile reste affiché sur ordinateur',
        'body' => "J'ai mis `hidden md:flex` sur mon menu d'ordinateur et `md:hidden` sur le bouton du menu mobile, mais le menu mobile ouvert reste visible quand j'agrandis la fenêtre.",
        'answers' => [
            ['author' => 'Ibrahima Diop', 'accepted' => true, 'body' => "Le panneau ouvert n'a probablement pas de classe responsive : seul le bouton est masqué. Ajoutez `md:hidden` au panneau aussi, ou fermez l'état quand on passe en grand écran.\n\n```jsx\n<nav className={`\${open ? 'block' : 'hidden'} md:hidden`}>…</nav>\n```"],
        ],
    ],
    [
        'author' => 'Serge Kouassi', 'days' => 20, 'votes' => 8, 'tags' => ['MTN MoMo API', 'PHP'],
        'title' => 'MTN MoMo : erreur 401 lors de la création du jeton d\'accès',
        'body' => "En sandbox, l'appel `POST /collection/token/` me renvoie 401. J'ai pourtant créé l'API user et l'API key.",
        'answers' => [
            ['author' => 'Kouadio Yao', 'accepted' => true, 'body' => "Vérifiez trois choses :\n\n1. L'en-tête `Ocp-Apim-Subscription-Key` doit être la clé du **produit Collection**, pas celle de Disbursement.\n2. L'authentification est en **Basic** : `base64(apiUser:apiKey)`.\n3. L'API user doit être créé avec un `X-Reference-Id` en **UUID v4**, sinon il est invalide sans message clair.\n\n```php\nHttp::withBasicAuth(\$apiUser, \$apiKey)\n    ->withHeaders(['Ocp-Apim-Subscription-Key' => \$collectionKey])\n    ->post(\"\$base/collection/token/\");\n```"],
            ['author' => 'Rodrigue Hounsou', 'body' => 'Et ne réutilisez pas le jeton au-delà de son `expires_in` (souvent 3 600 s) : mettez-le en cache avec cette durée moins une marge.'],
        ],
    ],
    [
        'author' => 'Grâce Adjovi', 'days' => 15, 'votes' => 5, 'tags' => ['UI/UX', 'Figma'],
        'title' => 'Concevoir un parcours de paiement pour des utilisateurs peu habitués au numérique',
        'body' => "Je dessine le parcours d'achat d'une appli de livraison à Cotonou. Beaucoup d'utilisateurs paient pour la première fois en ligne. Quelles bonnes pratiques ?",
        'answers' => [
            ['author' => 'Awa Seck', 'accepted' => true, 'body' => "- **Un écran, une décision.** Montant, opérateur, numéro, confirmation : pas tout à la fois.\n- **Montrez le montant en FCFA en gros** à chaque étape, sans surprise de frais à la fin.\n- **Pré-remplissez le numéro** si vous le connaissez, et affichez l'opérateur détecté.\n- **Expliquez ce qui va se passer** : « Vous allez recevoir une demande de validation sur votre téléphone ».\n- **Prévoyez l'échec** : message clair et bouton « Réessayer » qui garde la commande."],
            ['author' => 'Hawa Soumah', 'body' => "Testez avec 5 vraies personnes au marché, pas avec votre équipe. On découvre toujours qu'un mot ou une icône ne veut rien dire pour elles."],
        ],
    ],
    [
        'author' => 'Kadiatou Keïta', 'days' => 25, 'votes' => 6, 'tags' => ['Django', 'PostgreSQL'],
        'title' => 'Django : comment importer un gros fichier Excel sans bloquer le serveur ?',
        'body' => "Nos enquêteurs envoient des fichiers de 50 000 lignes. L'import dans la vue fait tomber le serveur en timeout.",
        'answers' => [
            ['author' => 'Arnaud Mbarga', 'accepted' => true, 'body' => "Sortez l'import de la requête HTTP :\n\n1. La vue enregistre le fichier et crée une tâche (Celery, ou `django-q` pour faire simple).\n2. La tâche lit le fichier par morceaux (`openpyxl` en `read_only=True`).\n3. Insérez par paquets de 1 000 avec `bulk_create`.\n4. L'utilisateur suit l'avancement sur une page qui interroge l'état de la tâche.\n\n```python\nfor batch in chunked(rows, 1000):\n    Record.objects.bulk_create([Record(**r) for r in batch])\n```"],
            ['author' => 'Fiacre Agossou', 'body' => 'Pour 50 000 lignes, un `COPY` PostgreSQL depuis un CSV est encore bien plus rapide si les données sont déjà propres.'],
        ],
    ],
    [
        'author' => 'Ousmane Sarr', 'days' => 30, 'votes' => 3, 'tags' => ['Odoo', 'Python'],
        'title' => 'Odoo : afficher les montants en FCFA sans décimales',
        'body' => "Sur les factures, Odoo affiche `150 000,00 FCFA`. En FCFA, on n'utilise pas de centimes. Comment les retirer partout ?",
        'answers' => [
            ['author' => 'Kadiatou Keïta', 'accepted' => true, 'body' => "Dans **Comptabilité > Configuration > Devises**, ouvrez la devise XOF et mettez l'**arrondi** à `1.0` : Odoo n'affichera plus de décimales et arrondira correctement les totaux. Vérifiez ensuite les modèles de rapport personnalisés qui forceraient un format."],
        ],
    ],
    [
        'author' => 'Aïssatou Diallo', 'days' => 18, 'votes' => 7, 'tags' => ['Wave API', 'Product management'],
        'title' => 'Wave, Orange Money ou agrégateur : par quoi commencer pour une startup au Sénégal ?',
        'body' => 'Nous lançons une appli de commande pour les commerçants de Dakar. Faut-il intégrer chaque opérateur ou passer par un agrégateur ?',
        'answers' => [
            ['author' => 'Cheikh Fall', 'accepted' => true, 'body' => "Pour démarrer vite, un **agrégateur** (PayDunya, CinetPay…) vous donne plusieurs opérateurs avec une seule intégration, au prix d'une commission un peu plus haute.\n\nQuand le volume grandit, intégrez en direct l'opérateur qui fait la majorité de vos encaissements (souvent Wave à Dakar) pour réduire les frais. Gardez une **couche d'abstraction** dans votre code (`PaymentGateway`) pour pouvoir changer sans tout réécrire."],
            ['author' => 'Moussa Ndiaye', 'body' => "Regardez aussi les délais de versement sur votre compte : ils changent beaucoup d'un prestataire à l'autre, et c'est souvent ce qui compte le plus pour la trésorerie d'un commerçant."],
        ],
    ],
    [
        'author' => 'Ibrahima Diop', 'days' => 33, 'votes' => 5, 'tags' => ['Node.js', 'TypeScript'],
        'title' => 'Comment éviter de débiter deux fois un client si l\'utilisateur clique deux fois ?',
        'body' => 'Sur mobile, certains utilisateurs cliquent deux fois sur « Payer » quand le réseau est lent. Nous avons eu des doubles débits.',
        'answers' => [
            ['author' => 'Jean-Marc N\'Guessan', 'accepted' => true, 'body' => "Utilisez une **clé d'idempotence** : le client génère un identifiant unique par tentative de paiement et l'envoie avec la requête. Le serveur l'enregistre avec un index unique : la deuxième requête renvoie le résultat de la première au lieu de redébiter.\n\n```ts\nconst key = crypto.randomUUID();\nawait fetch('/payments', { method: 'POST', headers: { 'Idempotency-Key': key }, body });\n```\n\nCôté interface, désactivez aussi le bouton pendant l'envoi, mais ne comptez jamais uniquement là-dessus."],
            ['author' => 'Kossi Mensah', 'body' => "Beaucoup d'API de paiement acceptent déjà un en-tête d'idempotence : transmettez-leur la même clé, vous serez protégé de bout en bout."],
        ],
    ],
    [
        'author' => 'Fatou Sow', 'days' => 22, 'votes' => 4, 'tags' => ['Flutter', 'Firebase'],
        'title' => 'Mon APK Flutter fait 60 Mo : comment le réduire ?',
        'body' => "Les utilisateurs se plaignent de la taille de l'appli. Beaucoup ont des téléphones avec peu de stockage.",
        'answers' => [
            ['author' => 'Christelle Ngo Biyong', 'accepted' => true, 'body' => "Publiez un **App Bundle** plutôt qu'un APK universel : le Play Store envoie à chaque téléphone uniquement son architecture.\n\n```bash\nflutter build appbundle --release --obfuscate --split-debug-info=build/symbols\n```\n\nPuis : compressez les images (WebP), retirez les polices inutiles et vérifiez les dépendances lourdes avec `flutter build apk --analyze-size`."],
            ['author' => 'Akossiwa Agbeko', 'body' => "Chez nous, une seule bibliothèque de cartes ajoutait 15 Mo. Le rapport d'analyse de taille l'a montré tout de suite."],
        ],
    ],
    [
        'author' => 'Yawo Amegbor', 'days' => 35, 'votes' => 6, 'tags' => ['Linux', 'DevOps', 'PostgreSQL'],
        'title' => 'Sauvegarder une base PostgreSQL chaque nuit vers un autre serveur',
        'body' => 'Quelle est la méthode la plus simple et fiable pour sauvegarder notre base chaque nuit hors du serveur de production ?',
        'answers' => [
            ['author' => 'Cheikh Fall', 'accepted' => true, 'body' => "Un `pg_dump` compressé, chiffré, envoyé ailleurs, et **testé** :\n\n```bash\npg_dump -Fc mabase | gpg --encrypt -r sauvegardes@exemple.sn > /tmp/mabase-$(date +%F).dump.gpg\nrclone copy /tmp/mabase-*.dump.gpg stockage:sauvegardes/\n```\n\nPlanifiez-le avec cron, gardez 7 sauvegardes quotidiennes et 4 hebdomadaires, et **restaurez une sauvegarde chaque mois** sur une machine de test. Une sauvegarde jamais restaurée n'est pas une sauvegarde."],
            ['author' => 'Jean-Marc N\'Guessan', 'body' => "Pour une base critique, ajoutez l'archivage des WAL (pgBackRest) : vous pourrez restaurer à la minute près, pas seulement à la nuit précédente."],
        ],
    ],
    [
        'author' => 'Seydou Touré', 'days' => 28, 'votes' => 3, 'tags' => ['WordPress', 'PHP'],
        'title' => 'Mon site WordPress a été piraté : par où commencer ?',
        'body' => "Le site d'un hôtel redirige vers des sites de paris. Je ne sais pas comment ils sont entrés.",
        'answers' => [
            ['author' => 'Mathieu Ahouansou', 'accepted' => true, 'body' => "Dans l'ordre :\n\n1. Mettez le site en maintenance et **changez tous les mots de passe** (admin, FTP, base, hébergeur).\n2. Restaurez une sauvegarde saine si vous en avez une, sinon réinstallez le cœur de WordPress.\n3. Cherchez les fichiers modifiés récemment et le code suspect (`eval(base64_decode(`).\n4. Supprimez les extensions et thèmes inutiles ou abandonnés : c'est l'entrée la plus fréquente.\n5. Mettez tout à jour, puis activez l'authentification à deux facteurs pour les comptes admin."],
        ],
    ],
    [
        'author' => 'Boukary Ouédraogo', 'days' => 45, 'votes' => 5, 'tags' => ['Laravel', 'Vue.js'],
        'title' => 'Laravel : générer des reçus PDF légers pour WhatsApp',
        'body' => 'Les écoles veulent envoyer les reçus de scolarité par WhatsApp. Nos PDF font 2 Mo à cause du logo.',
        'answers' => [
            ['author' => 'Paul Fotso', 'accepted' => true, 'body' => "Le logo est sûrement intégré en PNG haute résolution. Redimensionnez-le à sa taille d'impression (environ 300 px de large) et compressez-le. Évitez aussi d'embarquer des polices complètes : une police système ou un sous-ensemble suffit.\n\nAvec DomPDF, un reçu simple doit faire moins de 50 Ko."],
            ['author' => 'Boukary Ouédraogo', 'body' => 'Merci ! Le logo faisait 1,8 Mo à lui seul. Le reçu fait maintenant 38 Ko.'],
        ],
    ],
    [
        'author' => 'Christelle Ngo Biyong', 'days' => 50, 'votes' => 4, 'tags' => ['Flutter', 'Firebase'],
        'title' => 'Firebase : réduire la facture des lectures Firestore',
        'body' => 'Notre appli de livraison a dépassé le quota gratuit en deux semaines. Comment limiter les lectures ?',
        'answers' => [
            ['author' => 'Kossi Mensah', 'accepted' => true, 'body' => "Les causes habituelles :\n\n- des **écouteurs en temps réel** sur des collections entières au lieu de requêtes filtrées et paginées ;\n- des écrans qui se reconstruisent et relancent la requête à chaque fois ;\n- l'absence de **cache local** (activez la persistance hors ligne).\n\nRegroupez aussi les compteurs dans un document agrégé au lieu de compter les documents à chaque affichage."],
        ],
    ],
    [
        'author' => 'Abdoul Kaboré', 'days' => 55, 'votes' => 4, 'tags' => ['Kotlin', 'Android'],
        'title' => 'Envoyer des SMS de rappel de rendez-vous sans exploser le budget',
        'body' => 'Nous envoyons un SMS la veille de chaque rendez-vous médical. Le coût devient important. Des idées ?',
        'answers' => [
            ['author' => 'Samuel Nkwenti', 'accepted' => true, 'body' => "Négociez un tarif en volume avec un fournisseur local plutôt qu'une plateforme internationale : l'écart est souvent de 3 à 5 fois. Raccourcissez le message sous 160 caractères, sans accents, pour qu'il tienne en un seul SMS. Et proposez WhatsApp en option à ceux qui l'utilisent."],
        ],
    ],
    [
        'author' => 'Paul Fotso', 'days' => 60, 'votes' => 6, 'tags' => ['Laravel', 'MySQL'],
        'title' => 'Gérer plusieurs points de vente dans une même appli de caisse',
        'body' => 'Un client a 4 boutiques. Faut-il une base par boutique ou une seule base ?',
        'answers' => [
            ['author' => 'Fatoumata Camara', 'accepted' => true, 'body' => "Une seule base, avec une colonne `store_id` sur les tables concernées et un **scope global** qui filtre automatiquement selon la boutique de l'utilisateur. C'est beaucoup plus simple à maintenir et à consolider (chiffre d'affaires global, stocks transférés).\n\n```php\nprotected static function booted(): void\n{\n    static::addGlobalScope('store', fn (\$q) => \$q->where('store_id', auth()->user()->store_id));\n}\n```\n\nUne base par client n'a de sens qu'en SaaS avec des contraintes d'isolement fortes."],
            ['author' => 'Kouadio Yao', 'body' => "Prévoyez dès le départ le mode dégradé : si la connexion d'une boutique tombe, la caisse doit continuer en local et se resynchroniser."],
        ],
    ],
    [
        'author' => 'Akossiwa Agbeko', 'days' => 14, 'votes' => 3, 'tags' => ['Flutter', 'Supabase'],
        'title' => 'Supabase ou Firebase pour une appli de santé communautaire ?',
        'body' => 'Nous suivons des consultations et des vaccinations. Les données sont sensibles. Que choisir ?',
        'answers' => [
            ['author' => 'Fiacre Agossou', 'accepted' => true, 'body' => "Pour des données de santé, regardez d'abord **où sont stockées les données** et ce qu'exige la loi de votre pays sur les données personnelles. Supabase repose sur PostgreSQL et peut être auto-hébergé, ce qui permet de garder les données dans le pays. Activez dans tous les cas la sécurité au niveau des lignes (RLS) et chiffrez les sauvegardes."],
        ],
    ],
    [
        'author' => 'Mamadou Bah', 'days' => 38, 'votes' => 5, 'tags' => ['Node.js', 'MongoDB'],
        'title' => 'Géolocaliser des livreurs à moto sans vider leur batterie',
        'body' => "Nos livreurs à Conakry se plaignent que l'appli vide leur batterie en une demi-journée.",
        'answers' => [
            ['author' => 'Kossi Mensah', 'accepted' => true, 'body' => 'Ne demandez pas la position toutes les secondes. Envoyez-la **seulement pendant une course**, toutes les 15 à 30 secondes, ou après un déplacement de 50 m. Utilisez la précision « équilibrée » plutôt que le GPS haute précision, et regroupez les envois quand le réseau est faible.'],
            ['author' => 'Samuel Nkwenti', 'body' => 'Côté serveur, un index géospatial (`2dsphere` dans MongoDB) rend la recherche du livreur le plus proche instantanée.'],
        ],
    ],
    [
        'author' => 'Arnaud Mbarga', 'days' => 65, 'votes' => 4, 'tags' => ['Django', 'Docker'],
        'title' => 'Docker en production sur un petit VPS : bonne idée ?',
        'body' => "Notre serveur a 2 Go de RAM. Docker n'est-il pas trop lourd ?",
        'answers' => [
            ['author' => 'Yawo Amegbor', 'accepted' => true, 'body' => "Docker lui-même consomme peu. Ce qui pèse, ce sont les conteneurs : sur 2 Go, évitez de lancer PostgreSQL, Redis et un outil de supervision lourd dans des conteneurs séparés en plus de l'appli. Limitez la mémoire de chaque conteneur (`mem_limit`) et surveillez le swap."],
        ],
    ],
    [
        'author' => 'Bakary Coulibaly', 'days' => 42, 'votes' => 3, 'tags' => ['Laravel', 'Orange Money'],
        'title' => 'Comment tester les paiements Orange Money dans mes tests automatisés ?',
        'body' => 'Je ne veux pas appeler la vraie API dans ma suite de tests.',
        'answers' => [
            ['author' => 'Moussa Ndiaye', 'accepted' => true, 'body' => "Avec Laravel, simulez les appels HTTP avec `Http::fake()` et vérifiez ce qui a été envoyé :\n\n```php\nHttp::fake([\n    'api.orange.com/*' => Http::response(['status' => 'SUCCESS', 'txnid' => 'OM123'], 200),\n]);\n\n\$this->post('/paiements', ['montant' => 5000])->assertRedirect();\n\nHttp::assertSent(fn (\$request) => \$request['amount'] === 5000);\n```\n\nTestez aussi les cas d'échec : timeout, réponse `FAILED`, callback reçu deux fois."],
        ],
    ],
    [
        'author' => 'Aya Koné', 'days' => 19, 'votes' => 4, 'tags' => ['React Native', 'TypeScript'],
        'title' => 'React Native : les notifications push ne s\'affichent pas sur certains téléphones',
        'body' => "Sur certains Android d'entrée de gamme, nos notifications n'arrivent pas quand l'appli est fermée.",
        'answers' => [
            ['author' => 'Ibrahima Diop', 'accepted' => true, 'body' => "Plusieurs fabricants tuent les applis en arrière-plan pour économiser la batterie. Envoyez des notifications **prioritaires** pour ce qui est important, utilisez un canal de notification Android explicite, et expliquez à l'utilisateur comment désactiver l'optimisation de batterie pour votre appli."],
        ],
    ],
    [
        'author' => 'Salimata Sawadogo', 'days' => 27, 'votes' => 3, 'tags' => ['Power BI', 'Data Science'],
        'title' => 'Power BI : actualiser un tableau de bord avec des fichiers sur Google Drive',
        'body' => 'Les coopératives déposent leurs fichiers sur Google Drive. Peut-on actualiser Power BI automatiquement ?',
        'answers' => [
            ['author' => 'Mariam Traoré', 'accepted' => true, 'body' => "Le plus fiable est de passer par un dossier synchronisé (OneDrive ou SharePoint) que Power BI sait actualiser en ligne. Sinon, un petit script qui copie chaque nuit les fichiers de Drive vers ce dossier fait l'affaire."],
        ],
    ],
    [
        'author' => 'Kossi Mensah', 'days' => 70, 'votes' => 5, 'tags' => ['React', 'Node.js'],
        'title' => 'Billetterie : générer un QR code infalsifiable',
        'body' => "Comment éviter qu'un billet soit copié ou fabriqué ?",
        'answers' => [
            ['author' => 'Jean-Marc N\'Guessan', 'accepted' => true, 'body' => "Le QR ne doit contenir qu'un **identifiant signé**, jamais les informations du billet. Signez-le avec une clé secrète (HMAC) ; au contrôle, vérifiez la signature puis marquez le billet comme utilisé en base, **une seule fois** (mise à jour conditionnelle). Une copie du QR sera refusée au deuxième passage."],
            ['author' => 'Edem Afenyo', 'body' => "Prévoyez un mode hors ligne pour les contrôleurs : téléchargez la liste des billets valides avant l'événement et synchronisez les passages ensuite."],
        ],
    ],
    [
        'author' => 'Fiacre Agossou', 'days' => 48, 'votes' => 6, 'tags' => ['Python', 'FastAPI'],
        'title' => 'FastAPI : limiter le nombre d\'appels par clé d\'API',
        'body' => 'Notre API de transcription est coûteuse. Comment limiter chaque client à 100 appels par heure ?',
        'answers' => [
            ['author' => 'Kadiatou Keïta', 'accepted' => true, 'body' => "Un compteur dans Redis par clé et par fenêtre d'une heure, vérifié dans une dépendance FastAPI :\n\n```python\nasync def rate_limit(key: str = Depends(api_key)):\n    window = f\"rl:{key}:{int(time.time() // 3600)}\"\n    count = await redis.incr(window)\n    if count == 1:\n        await redis.expire(window, 3600)\n    if count > 100:\n        raise HTTPException(429, \"Limite horaire atteinte\")\n```"],
        ],
    ],
    [
        'author' => 'Mathieu Ahouansou', 'days' => 52, 'votes' => 4, 'tags' => ['Symfony', 'PHP'],
        'title' => 'Symfony : calculer correctement l\'IRPP sur des bulletins de paie',
        'body' => 'Nos calculs de retenues diffèrent de quelques francs de ceux du cabinet comptable.',
        'answers' => [
            ['author' => 'Rodrigue Hounsou', 'accepted' => true, 'body' => "Presque toujours une question d'**arrondi** : arrondissez à chaque étape exactement comme le barème officiel (souvent à la dizaine inférieure sur la base imposable), et jamais sur des flottants. Travaillez en entiers (francs) ou avec une bibliothèque décimale, et écrivez des tests avec les exemples chiffrés du cabinet."],
        ],
    ],
    [
        'author' => 'Grâce Adjovi', 'days' => 11, 'votes' => 2, 'tags' => ['UI/UX'],
        'title' => 'Faut-il traduire l\'interface en fon ou en wolof pour un premier lancement ?',
        'body' => 'Nos utilisateurs parlent surtout fon au quotidien, mais lisent le français.',
        'answers' => [
            ['author' => 'Fiacre Agossou', 'body' => 'Commencez en français, mais prévoyez la traduction dès maintenant (aucun texte écrit en dur). Pour les messages critiques (confirmation de paiement, erreurs), des **messages vocaux** en langue locale marchent souvent mieux que du texte.'],
        ],
    ],
    [
        'author' => 'Edem Afenyo', 'days' => 36, 'votes' => 4, 'tags' => ['PayDunya', 'PHP'],
        'title' => 'PayDunya : la redirection après paiement ne garde pas la session',
        'body' => "Après paiement, l'utilisateur revient sur mon site déconnecté.",
        'answers' => [
            ['author' => 'Fatoumata Camara', 'accepted' => true, 'body' => "C'est le cookie de session qui n'est pas renvoyé lors du retour depuis un autre domaine. Mettez `SESSION_SAME_SITE=lax` (pas `strict`) et vérifiez que le retour se fait bien en HTTPS sur le même domaine exact (avec ou sans `www`)."],
        ],
    ],
    [
        'author' => 'Cheikh Fall', 'days' => 80, 'votes' => 7, 'tags' => ['DevOps', 'Docker', 'Linux'],
        'title' => 'Mettre en place des alertes par SMS quand un serveur tombe',
        'body' => 'Nos équipes ne regardent pas leurs e-mails la nuit. Quelle solution simple pour être alerté par SMS ?',
        'answers' => [
            ['author' => 'Yawo Amegbor', 'accepted' => true, 'body' => "Un outil de supervision qui vérifie vos URL toutes les minutes, relié à un webhook qui envoie le SMS via votre fournisseur. Ne déclenchez l'alerte qu'après **deux échecs consécutifs**, sinon une micro-coupure réveille tout le monde pour rien."],
            ['author' => 'Jean-Marc N\'Guessan', 'body' => "Faites tourner la supervision **hors** de l'infrastructure surveillée : si tout le datacenter tombe, la sonde doit encore pouvoir prévenir."],
        ],
    ],
    [
        'author' => 'Kouadio Yao', 'days' => 90, 'votes' => 8, 'tags' => ['Java', 'Spring Boot', 'MTN MoMo API'],
        'title' => 'Rapprocher automatiquement les paiements Mobile Money avec les factures',
        'body' => 'Nos clients paient parfois un montant différent ou sans référence. Comment rapprocher automatiquement ?',
        'answers' => [
            ['author' => 'Jean-Marc N\'Guessan', 'accepted' => true, 'body' => "Par étapes de confiance décroissante :\n\n1. **Référence exacte** dans le motif du paiement → rapprochement automatique.\n2. **Numéro de téléphone connu + montant exact** sur les factures ouvertes → automatique.\n3. **Montant exact sans numéro connu** → proposé à un humain.\n4. Le reste → file de traitement manuel.\n\nGardez une trace de chaque décision : le jour d'un litige, vous devez pouvoir expliquer pourquoi tel paiement a soldé telle facture."],
            ['author' => 'Kouadio Yao', 'body' => "Merci, c'est exactement la logique qui a fonctionné. 85 % des paiements passent maintenant aux étapes 1 et 2."],
        ],
    ],
    [
        'author' => 'Awa Seck', 'days' => 16, 'votes' => 3, 'tags' => ['Figma', 'UI/UX'],
        'title' => 'Quelle taille de texte minimale pour une appli utilisée en plein soleil ?',
        'body' => 'Nos utilisateurs sont souvent dehors, au marché.',
        'answers' => [
            ['author' => 'Grâce Adjovi', 'accepted' => true, 'body' => '16 px minimum pour le texte courant, des contrastes forts (au moins 4,5:1, plutôt 7:1 pour les montants) et des zones tactiles de 44 px. Évitez le gris clair sur blanc : il disparaît au soleil.'],
        ],
    ],
    // Questions récentes sans réponse (pour le filtre « Sans réponse »).
    [
        'author' => 'Mariama Ba', 'days' => 0, 'votes' => 1, 'tags' => ['Django', 'React'],
        'title' => 'Django REST et React : où stocker le jeton d\'authentification ?',
        'body' => 'Je débute avec une API Django REST et un front React. Faut-il stocker le jeton dans `localStorage` ou dans un cookie ?',
        'answers' => [],
    ],
    [
        'author' => 'Oumou Dembélé', 'days' => 1, 'votes' => 2, 'tags' => ['Flutter'],
        'title' => 'Afficher des montants en FCFA avec un espace comme séparateur en Flutter',
        'body' => "`NumberFormat` m'affiche `150,000`. Je voudrais `150 000 FCFA`.",
        'answers' => [],
    ],
    [
        'author' => 'Alpha Barry', 'days' => 2, 'votes' => 1, 'tags' => ['PHP', 'MySQL'],
        'title' => 'Gérer les inscriptions scolaires quand la connexion coupe en plein formulaire',
        'body' => "À Kankan, les parents perdent souvent ce qu'ils ont saisi. Comment éviter ça sans application mobile ?",
        'answers' => [],
    ],
    [
        'author' => 'Brenda Tchoupo', 'days' => 0, 'votes' => 0, 'tags' => ['Next.js'],
        'title' => 'Next.js : mes images ne s\'affichent pas après le déploiement',
        'body' => 'En local tout marche, mais sur le serveur les images du dossier `public` renvoient une 404.',
        'answers' => [],
    ],
];
