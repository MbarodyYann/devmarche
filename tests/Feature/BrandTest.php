<?php

use App\Support\Brand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

test('le cauri de réputation et celui du logo partagent les mêmes tracés', function () {
    $cauri = Blade::render('<x-cauri />');
    $mark = Blade::render('<x-logo-mark />');

    foreach ([Brand::CAURI_OUTER, Brand::CAURI_SHELL, Brand::CAURI_APERTURE] as $path) {
        expect($cauri)->toContain($path)
            ->and($mark)->toContain($path);
    }
});

test('le favicon ne garde que la fenêtre et le code, sans cauri ni points', function () {
    $small = Blade::render('<x-logo-mark variant="small" />');

    expect($small)->toContain(Brand::CHEVRONS)
        ->not->toContain(Brand::CAURI_OUTER)
        ->not->toContain('<circle');
});

test('les fichiers d\'identité existent et le symbole reste léger', function () {
    foreach (['favicon.ico', 'favicon.svg', 'apple-touch-icon.png', 'site.webmanifest', 'images/logo-mark.svg', 'images/logo-mark-mono.svg', 'images/logo.svg', 'images/icon-512.png', 'images/og-default.png'] as $file) {
        expect(public_path($file))->toBeFile();
    }

    expect(filesize(public_path('images/logo-mark.svg')))->toBeLessThan(2200)
        ->and(array_slice(getimagesize(public_path('images/og-default.png')), 0, 2))->toBe([1200, 630])
        ->and(json_decode(File::get(public_path('site.webmanifest')), true)['theme_color'])->toBe('#314A82');
});

test('les pages annoncent le favicon, le manifeste et l\'image de partage', function () {
    $this->get('/devs')
        ->assertSee('<link rel="manifest" href="/site.webmanifest">', false)
        ->assertSee('<meta name="theme-color" content="#314A82">', false)
        ->assertSee('images/og-default.png', false)
        ->assertSee('property="og:title"', false);
});

test('aucun dégradé dans les styles et les vues (charte v3 : aplats uniquement)', function () {
    $files = collect(File::allFiles(resource_path()))
        ->filter(fn ($file) => in_array($file->getExtension(), ['css', 'php', 'js']));

    foreach ($files as $file) {
        expect(File::get($file->getPathname()))
            ->not->toMatch('/gradient|bg-linear|bg-clip-text/i', $file->getRelativePathname());
    }
});

test('les couleurs de la marque sont l\'indigo cobalt et l\'ambre', function () {
    $css = File::get(resource_path('css/app.css'));

    expect($css)->toContain('--color-primary-600: #314a82;')
        ->toContain('--color-secondary-400: #f9b218;')
        ->not->toContain('#c4532b');

    expect(File::get(public_path('images/logo-mark.svg')))
        ->toContain(Brand::PRIMARY_600)
        ->toContain(Brand::SECONDARY_400);
});
