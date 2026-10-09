<?php

namespace App\Console\Commands;

use App\Support\Brand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Génère les fichiers statiques de l'identité à partir de App\Support\Brand (source unique).
 * Les PNG (favicons, icônes d'application, image Open Graph) sont ensuite rendus depuis ces SVG
 * par scripts/brand-png.sh (Chrome headless), puis commités.
 */
class ExportBrand extends Command
{
    protected $signature = 'brand:export';

    protected $description = 'Génère les SVG du logo, du favicon et de l\'image Open Graph dans public/';

    public function handle(): int
    {
        File::ensureDirectoryExists(public_path('images'));

        $files = [
            'images/logo-mark.svg' => $this->svg(Brand::MARK_VIEWBOX, $this->mark()),
            'images/logo-mark-mono.svg' => $this->svg(Brand::MARK_VIEWBOX, $this->mono(), 'color:'.Brand::SECONDARY_700),
            'images/logo.svg' => $this->logo(Brand::INK),
            'images/logo-dark.svg' => $this->logo(Brand::INK_DARK, '#0F1126'),
            'favicon.svg' => $this->svg(Brand::FAVICON_VIEWBOX, $this->mark(small: true)),
            'images/app-icon.svg' => $this->appIcon(),
            'images/og-default.svg' => $this->openGraph(),
        ];

        foreach ($files as $path => $content) {
            File::put(public_path($path), $content."\n");
            $this->line(sprintf('  %-28s %5d octets', $path, strlen($content)));
        }

        $this->info('Fichiers générés. Rendu des PNG : scripts/brand-png.sh');

        return self::SUCCESS;
    }

    private function svg(string $viewBox, string $body, ?string $style = null): string
    {
        $style = $style ? ' style="'.$style.'"' : '';

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="'.$viewBox.'"'.$style.'>'.$body.'</svg>';
    }

    /** Symbole en couleurs. $gap : couleur de la marge autour du cauri (celle du fond). */
    private function mark(bool $small = false, string $gap = '#FFFFFF'): string
    {
        $p = Brand::PRIMARY_500;
        $s = Brand::SECONDARY_700;

        $out = '<path fill="'.$p.'" d="'.Brand::WINDOW.'"/>'
            .'<path fill="'.$s.'" d="'.Brand::WINDOW_BODY.'"/>';

        if (! $small) {
            $out .= '<g fill="#fff"><circle cx="24.4" cy="26.2" r="2.1"/><circle cx="31.1" cy="26.2" r="2.1"/><circle cx="37.7" cy="26.2" r="2.1"/></g>';
        }

        $out .= '<g fill="none" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">'
            .'<path stroke="#fff" d="'.Brand::CHEVRONS.'"/><path stroke="'.$p.'" d="'.Brand::SLASH.'"/></g>';

        if (! $small) {
            $out .= '<g transform="'.Brand::CAURI_TRANSFORM.'">'
                .'<path fill="'.$gap.'" stroke="'.$gap.'" stroke-width="1.4" d="'.Brand::CAURI_OUTER.'"/>'
                .'<path fill="'.$p.'" d="'.Brand::CAURI_OUTER.'"/>'
                .'<path fill="#fff" d="'.Brand::CAURI_SHELL.'"/>'
                .'<path fill="'.$p.'" d="'.Brand::CAURI_APERTURE.'"/></g>';
        }

        return $out;
    }

    /** Version d'une seule couleur (currentColor), avec découpes par masque. */
    private function mono(): string
    {
        return '<mask id="m"><rect width="100" height="100" fill="#fff"/>'
            .'<g fill="#000"><circle cx="24.4" cy="26.2" r="2.1"/><circle cx="31.1" cy="26.2" r="2.1"/><circle cx="37.7" cy="26.2" r="2.1"/></g>'
            .'<path d="M17 30.5h60" stroke="#000" stroke-width="1.2"/>'
            .'<path d="'.Brand::CHEVRONS.' '.Brand::SLASH.'" fill="none" stroke="#000" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/>'
            .'<path transform="'.Brand::CAURI_TRANSFORM.'" fill="#000" stroke="#000" stroke-width="1.4" d="'.Brand::CAURI_OUTER.'"/></mask>'
            .'<path fill="currentColor" mask="url(#m)" d="'.Brand::WINDOW.'"/>'
            .'<g fill="currentColor" transform="'.Brand::CAURI_TRANSFORM.'">'
            .'<path fill-rule="evenodd" d="'.Brand::CAURI_OUTER.Brand::CAURI_SHELL.'"/><path d="'.Brand::CAURI_APERTURE.'"/></g>';
    }

    /** Symbole + mot « DevMarché » côte à côte. */
    private function logo(string $ink, string $gap = '#FFFFFF'): string
    {
        [, , $ww, $wh] = array_map('floatval', explode(' ', Brand::WORDMARK_VIEWBOX));
        $scale = 40 / $wh; // mot de 40 unités de haut, symbole de 78

        return $this->svg('0 0 '.round(88 + $ww * $scale).' 78',
            '<svg x="0" y="0" width="78" height="78" viewBox="'.Brand::MARK_VIEWBOX.'">'.$this->mark(gap: $gap).'</svg>'
            .'<svg x="88" y="19" width="'.round($ww * $scale, 1).'" height="40" viewBox="'.Brand::WORDMARK_VIEWBOX.'">'
            .'<path fill="'.$ink.'" d="'.Brand::WORDMARK_BODY.'"/><path fill="'.Brand::PRIMARY_500.'" d="'.Brand::WORDMARK_ACCENT.'"/></svg>');
    }

    /** Icône d'application : symbole complet sur fond blanc, marge de 12 %. */
    private function appIcon(): string
    {
        return $this->svg('0 0 100 100',
            '<rect width="100" height="100" fill="#fff"/>'
            .'<svg x="12" y="12" width="76" height="76" viewBox="'.Brand::MARK_VIEWBOX.'">'.$this->mark().'</svg>');
    }

    /** Image de partage par défaut (1 200 × 630). */
    private function openGraph(): string
    {
        $tagline = trim(File::get(resource_path('brand/tagline.path')));
        $meta = json_decode(File::get(resource_path('brand/tagline.json')), true);
        [, , $ww, $wh] = array_map('floatval', explode(' ', Brand::WORDMARK_VIEWBOX));
        $wordWidth = round($ww * 110 / $wh, 1);
        $groupWidth = 190 + 36 + $wordWidth;
        $x = round((1200 - $groupWidth) / 2, 1);
        $taglineX = round((1200 - $meta['width']) / 2 - $meta['x1'], 1);

        return $this->svg('0 0 1200 630',
            '<defs><pattern id="p" width="24" height="24" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.4" fill="#171426" fill-opacity=".07"/></pattern>'
            .'</defs>'
            .'<rect width="1200" height="630" fill="#FAF8F5"/><rect width="1200" height="630" fill="url(#p)"/>'
            .'<rect y="618" width="600" height="12" fill="'.Brand::PRIMARY_500.'"/><rect x="600" y="618" width="600" height="12" fill="'.Brand::SECONDARY_700.'"/>'
            .'<svg x="'.$x.'" y="170" width="190" height="190" viewBox="'.Brand::MARK_VIEWBOX.'">'.$this->mark(gap: '#FAF8F5').'</svg>'
            .'<svg x="'.($x + 226).'" y="210" width="'.$wordWidth.'" height="110" viewBox="'.Brand::WORDMARK_VIEWBOX.'">'
            .'<path fill="'.Brand::INK.'" d="'.Brand::WORDMARK_BODY.'"/><path fill="'.Brand::PRIMARY_500.'" d="'.Brand::WORDMARK_ACCENT.'"/></svg>'
            .'<path transform="translate('.$taglineX.' 450)" fill="'.Brand::SECONDARY_700.'" d="'.$tagline.'"/>');
    }
}
