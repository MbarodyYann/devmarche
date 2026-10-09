<?php

use App\Models\Mission;
use App\Services\MarkdownRenderer;

function md(string $markdown): string
{
    return app(MarkdownRenderer::class)->toHtml($markdown);
}

test('le HTML brut est échappé (pas de <script>, pas d\'attributs on…)', function () {
    $html = md("Bonjour <script>alert('xss')</script> et <img src=x onerror=alert(1)> <a href=\"#\" onclick=\"vol()\">clic</a>");

    expect($html)->not->toContain('<script')
        ->not->toContain('<img')
        ->not->toContain('<a href="#" onclick')
        ->toContain('&lt;script&gt;');
});

test('les liens javascript: et data: sont neutralisés', function () {
    expect(md('[piège](javascript:alert(1))'))->not->toContain('javascript:')
        ->and(md('[piège](data:text/html;base64,PHNjcmlwdD4=)'))->not->toContain('data:text/html')
        ->and(md('![x](javascript:alert(1))'))->not->toContain('javascript:');
});

test('les liens externes ont rel="nofollow noopener noreferrer"', function () {
    expect(md('[Wave](https://docs.wave.com)'))->toContain('rel="nofollow noopener noreferrer"');
});

test('les blocs de code sont colorés côté serveur, échappés, avec un bouton Copier', function () {
    $html = md("```php\necho '<b>' . \$x;\n```");

    expect($html)->toContain('class="hljs language-php"')
        ->toContain('hljs-keyword')
        ->toContain('&lt;b&gt;')
        ->toContain('Copier')
        ->not->toContain('<b>');
});

test('un langage inconnu est affiché échappé, sans erreur', function () {
    expect(md("```cobol\nDISPLAY '<i>'.\n```"))->toContain('&lt;i&gt;')->toContain('code-block');
});

test('la description d\'une mission est rendue en Markdown sécurisé', function () {
    $mission = Mission::factory()->create(['description' => "**Livrables** :\n\n- API\n- tests\n\n<script>x</script>"]);

    expect($mission->description_html)->toContain('<strong>Livrables</strong>')->toContain('<li>API</li>')->not->toContain('<script>');

    $this->get(route('missions.show', $mission))->assertSee('<strong>Livrables</strong>', false)->assertDontSee('<script>x</script>', false);
});
