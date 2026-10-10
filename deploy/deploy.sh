#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Déploiement de DevMarché (idempotent : on peut le relancer sans risque).
# À lancer sur le serveur, par l'utilisateur « devmarche », depuis n'importe où :
#     /var/www/devmarche/deploy/deploy.sh
# ---------------------------------------------------------------------------
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/devmarche}"
BRANCH="${BRANCH:-main}"
PHP="${PHP:-php8.2}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.2-fpm}"

cd "$APP_DIR"

step() { printf '\n\033[1m==> %s\033[0m\n' "$1"; }

step "Mode maintenance (les visiteurs voient une page d'attente)"
# Ignoré au tout premier déploiement (pas encore de vendor/).
if [[ -f vendor/autoload.php ]]; then
    "$PHP" artisan down --retry=15 || true
fi
# Quoi qu'il arrive, on sort du mode maintenance à la fin du script.
trap '"$PHP" artisan up >/dev/null 2>&1 || true' EXIT

step "Code : branche $BRANCH"
git fetch --prune origin
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

step "Dépendances PHP (sans les outils de développement)"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

step "Fichiers CSS et JS"
npm ci --no-audit --no-fund
npm run build
# node_modules ne sert qu'à construire : on le supprime pour alléger le serveur.
rm -rf node_modules

step "Base de données"
"$PHP" artisan migrate --force

step "Lien public des avatars"
[[ -L public/storage ]] || "$PHP" artisan storage:link

step "Caches de production"
"$PHP" artisan optimize:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache
"$PHP" artisan event:cache

step "Redémarrage de PHP-FPM (vide l'OPcache)"
sudo systemctl reload "$PHP_FPM_SERVICE"

step "Vérification"
"$PHP" artisan about --only=environment

printf '\n\033[1;32mDéploiement terminé.\033[0m\n'
