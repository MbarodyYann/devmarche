<?php

/*
| Régénère les images du hero en WebP depuis les sources PNG (documentation/assets/, hors dépôt).
| Usage : php scripts/hero-images.php
| Outil de développement (PHP Imagick). Les sources ne sont jamais servies telles quelles.
|
| Pour chaque variante, la qualité descend de 78 à 60 jusqu'à tenir le poids maximal.
*/

$root = dirname(__DIR__);
$variants = [
    // [source, sortie, largeur, hauteur, poids maximal en octets]
    ['hero-portrait-source.png', 'portrait-480.webp', 480, 600, 40_000],
    ['hero-portrait-source.png', 'portrait-800.webp', 800, 1000, 80_000],
    ['hero-paysage-source.png', 'paysage-640.webp', 640, 360, 40_000],
    // Téléphones à densité ×1,5 à ×2 (le plus courant) : l'image LCP sur mobile, à garder légère en 3G.
    ['hero-paysage-source.png', 'paysage-720.webp', 720, 405, 40_000],
    ['hero-paysage-source.png', 'paysage-960.webp', 960, 540, 70_000],
];

@mkdir("$root/public/images/hero", 0755, true);

foreach ($variants as [$source, $output, $width, $height, $maxBytes]) {
    foreach ([78, 75, 72, 68, 64, 60] as $quality) {
        $image = new Imagick("$root/documentation/assets/$source");
        $image->cropThumbnailImage($width, $height); // recadrage centré au ratio exact, puis redimensionnement
        $image->stripImage();
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($quality);
        $blob = $image->getImageBlob();

        if (strlen($blob) <= $maxBytes) {
            break;
        }
    }

    file_put_contents("$root/public/images/hero/$output", $blob);
    printf("  public/images/hero/%-18s %4d × %-4d  qualité %d  %6d octets (max %d)\n", $output, $width, $height, $quality, strlen($blob), $maxBytes);
}
