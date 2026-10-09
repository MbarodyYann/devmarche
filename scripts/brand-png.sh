#!/usr/bin/env bash
# Rend les PNG de l'identité depuis les SVG générés par « php artisan brand:export ».
# Outil de développement uniquement (Chrome headless + PHP Imagick), à relancer si le logo change.
set -euo pipefail
cd "$(dirname "$0")/.."

CHROME="${CHROME:-/Applications/Google Chrome.app/Contents/MacOS/Google Chrome}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

render() { # svg largeur hauteur sortie [fond transparent]
    local html="$TMP/page.html"
    printf '<!doctype html><html><body style="margin:0;background:transparent"><img src="file://%s" style="display:block;width:%spx;height:%spx"></body></html>' \
        "$PWD/public/$1" "$2" "$3" > "$html"
    "$CHROME" --headless=new --disable-gpu --hide-scrollbars --force-device-scale-factor=1 \
        --default-background-color=00000000 --window-size="$2,$3" --screenshot="$PWD/public/$4" "file://$html" >/dev/null 2>&1
    echo "  public/$4 ($2 × $3)"
}

render favicon.svg 32 32 favicon-32.png
render favicon.svg 16 16 favicon-16.png
render images/app-icon.svg 180 180 apple-touch-icon.png
render images/app-icon.svg 192 192 images/icon-192.png
render images/app-icon.svg 512 512 images/icon-512.png
render images/og-default.svg 1200 630 images/og-default.png

php -r '
$ico = new Imagick();
foreach (["public/favicon-16.png", "public/favicon-32.png"] as $f) { $i = new Imagick($f); $i->setImageFormat("ico"); $ico->addImage($i); }
$ico->writeImages("public/favicon.ico", true);
echo "  public/favicon.ico (16 et 32)\n";
'
