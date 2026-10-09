<?php

namespace App\Models\Concerns;

use App\Services\MarkdownRenderer;

/**
 * Rend un champ Markdown en HTML sécurisé à l'enregistrement, uniquement s'il a changé.
 * Le modèle déclare la correspondance dans markdownFields() : ['body' => 'body_html'].
 */
trait RendersMarkdown
{
    /**
     * @return array<string, string>
     */
    abstract protected function markdownFields(): array;

    protected static function bootRendersMarkdown(): void
    {
        static::saving(function (self $model) {
            foreach ($model->markdownFields() as $source => $target) {
                if ($model->isDirty($source) || $model->getAttribute($target) === null) {
                    $model->setAttribute($target, app(MarkdownRenderer::class)->toHtml((string) $model->getAttribute($source)));
                }
            }
        });
    }
}
