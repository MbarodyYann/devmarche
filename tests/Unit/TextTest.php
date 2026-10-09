<?php

use App\Support\Seo;
use App\Support\Text;

test('les extraits retirent la syntaxe Markdown et le HTML', function () {
    $markdown = "Sur le serveur, le dossier `public` renvoie une **404**. Voir [la doc](https://exemple.test) :\n```php\n\$a = 1;\n```\n- un point\n## Titre\n> citation\n_italique_ et snake_case_name";

    expect(Text::plain($markdown))->toBe('Sur le serveur, le dossier public renvoie une 404. Voir la doc : $a = 1; un point Titre citation italique et snake_case_name')
        ->and(Text::plain('<p>Le dossier <code>public</code> &amp; le reste</p>'))->toBe('Le dossier public & le reste');
});

test('extrait coupé proprement, et description par défaut si vide', function () {
    expect(mb_strlen(Text::excerpt(str_repeat('Mobile Money ', 40), 160)))->toBe(160)
        ->and(Text::excerpt(str_repeat('a', 300), 50))->toEndWith('…')
        ->and(Seo::excerpt('``'))->toBe(Seo::DEFAULT_DESCRIPTION);
});
