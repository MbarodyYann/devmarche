<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Tableau des licences de la page « À propos », lu dans les fichiers de verrouillage
 * (composer.lock, package-lock.json) pour ne jamais diverger des versions installées.
 */
class Credits
{
    /**
     * @return list<array{name: string, version: string, license: string, usage: string}>
     */
    public static function licenses(): array
    {
        return Cache::remember('credits.licenses', 3600, function () {
            $installed = self::installed();

            $packages = array_map(function (array $item) use ($installed) {
                $found = $installed[$item['type']][$item['package']] ?? null;

                return [
                    'name' => $item['name'],
                    'version' => ltrim($found['version'] ?? '—', 'v'),
                    'license' => $item['license'] ?? $found['license'] ?? '—',
                    'usage' => $item['usage'],
                ];
            }, config('credits.packages'));

            return [...$packages, ...config('credits.assets')];
        });
    }

    /**
     * Paquets installés : [type => [nom => [version, licence]]].
     *
     * @return array{composer: array<string, array{version: string, license: string}>, npm: array<string, array{version: string, license: string}>}
     */
    public static function installed(): array
    {
        $composer = self::json(base_path('composer.lock'));
        $npm = self::json(base_path('package-lock.json'));

        $fromComposer = collect([...($composer['packages'] ?? []), ...($composer['packages-dev'] ?? [])])
            ->mapWithKeys(fn (array $p) => [$p['name'] => ['version' => $p['version'], 'license' => implode(' ou ', $p['license'] ?? [])]]);

        $fromNpm = collect($npm['packages'] ?? [])
            ->filter(fn (array $p, string $path) => str_starts_with($path, 'node_modules/'))
            ->mapWithKeys(fn (array $p, string $path) => [substr($path, strlen('node_modules/')) => ['version' => $p['version'] ?? '—', 'license' => $p['license'] ?? '—']]);

        return ['composer' => $fromComposer->all(), 'npm' => $fromNpm->all()];
    }

    /**
     * @return array<string, mixed>
     */
    private static function json(string $path): array
    {
        return is_file($path) ? (json_decode((string) file_get_contents($path), true) ?: []) : [];
    }
}
