<?php

namespace App\Markdown;

use Highlight\Highlighter;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

/**
 * Blocs de code : coloration côté serveur (highlight.php), police mono, défilement horizontal
 * dans le bloc, et bouton « Copier » (Alpine). Aucun JS de coloration n'est envoyé au client.
 */
class HighlightedCodeRenderer implements NodeRendererInterface
{
    /** Langages chargés (et seuls candidats de la détection automatique), pour la performance. */
    public const LANGUAGES = ['php', 'javascript', 'typescript', 'python', 'dart', 'kotlin', 'java', 'sql', 'bash', 'json', 'yaml', 'xml', 'css', 'go'];

    /** Alias courants écrits après les ``` → langage chargé. */
    private const ALIASES = [
        'js' => 'javascript', 'ts' => 'typescript', 'py' => 'python', 'sh' => 'bash', 'shell' => 'bash',
        'console' => 'bash', 'yml' => 'yaml', 'html' => 'xml', 'blade' => 'xml', 'vue' => 'xml', 'kt' => 'kotlin',
        'golang' => 'go', 'postgres' => 'sql', 'postgresql' => 'sql', 'mysql' => 'sql',
    ];

    private static ?Highlighter $highlighter = null;

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        /** @var FencedCode|IndentedCode $node */
        $code = rtrim($node->getLiteral(), "\n");
        $requested = $node instanceof FencedCode ? strtolower(trim(explode(' ', (string) $node->getInfo())[0])) : '';
        $language = self::ALIASES[$requested] ?? $requested;

        [$html, $language] = $this->highlight($code, in_array($language, self::LANGUAGES, true) ? $language : null);

        $label = $language ? '<span class="code-lang">'.e($language).'</span>' : '<span></span>';

        return '<div class="code-block" x-data="{ copied: false }">'
            .'<div class="code-toolbar">'.$label
            .'<button type="button" class="code-copy" x-on:click="navigator.clipboard.writeText($refs.code.innerText); copied = true; setTimeout(() => copied = false, 1500)"'
            .' x-text="copied ? \'Copié\' : \'Copier\'">Copier</button></div>'
            // tabindex : un bloc qui défile horizontalement doit être atteignable au clavier.
            .'<pre tabindex="0" aria-label="'.e('Code'.($language ? ' '.$language : '')).'"><code x-ref="code" class="hljs'.($language ? ' language-'.e($language) : '').'">'.$html.'</code></pre></div>'."\n";
    }

    /**
     * @return array{0: string, 1: ?string} HTML échappé et coloré, et langage retenu.
     */
    private function highlight(string $code, ?string $language): array
    {
        try {
            $highlighter = self::highlighter();
            $result = $language ? $highlighter->highlight($language, $code) : $highlighter->highlightAuto($code);

            return [$result->value, $result->language ?: $language];
        } catch (\Throwable) {
            return [e($code), $language];
        }
    }

    private static function highlighter(): Highlighter
    {
        if (self::$highlighter === null) {
            $highlighter = new Highlighter(false); // aucun langage chargé par défaut
            $dir = base_path('vendor/scrivo/highlight.php/Highlight/languages');

            foreach (self::LANGUAGES as $language) {
                Highlighter::registerLanguage($language, "$dir/$language.json");
            }

            $highlighter->setAutodetectLanguages(self::LANGUAGES);
            self::$highlighter = $highlighter;
        }

        return self::$highlighter;
    }
}
