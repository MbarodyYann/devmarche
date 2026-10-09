<?php

namespace App\Services;

use App\Markdown\HighlightedCodeRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Markdown sécurisé (questions, réponses, descriptions de missions).
 *
 * - Le HTML brut est échappé (html_input: escape) : <script>, onerror=… restent du texte.
 * - Les liens dangereux (javascript:, data:…) sont neutralisés (allow_unsafe_links: false).
 * - Les liens externes reçoivent rel="nofollow noopener noreferrer" et s'ouvrent dans un nouvel onglet.
 * - Les blocs de code sont colorés côté serveur (voir HighlightedCodeRenderer).
 *
 * Le HTML produit est stocké en base (colonnes *_html) au moment de l'enregistrement :
 * on ne recolore pas à chaque affichage.
 */
class MarkdownRenderer
{
    private ?MarkdownConverter $converter = null;

    public function toHtml(string $markdown): string
    {
        return (string) $this->converter()->convert($markdown);
    }

    private function converter(): MarkdownConverter
    {
        if ($this->converter === null) {
            $environment = new Environment([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 20,
                'renderer' => ['soft_break' => "<br>\n"],
                'external_link' => [
                    'internal_hosts' => [parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost'],
                    'open_in_new_window' => true,
                    'nofollow' => 'external',
                    'noopener' => 'external',
                    'noreferrer' => 'external',
                ],
            ]);
            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new GithubFlavoredMarkdownExtension);
            $environment->addExtension(new ExternalLinkExtension);

            $code = new HighlightedCodeRenderer;
            $environment->addRenderer(FencedCode::class, $code, 10);
            $environment->addRenderer(IndentedCode::class, $code, 10);

            $this->converter = new MarkdownConverter($environment);
        }

        return $this->converter;
    }
}
