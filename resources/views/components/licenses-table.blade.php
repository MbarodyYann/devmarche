@props(['rows'])

{{-- Sur mobile, chaque ligne devient un bloc empilé (pas de défilement horizontal à 360 px). --}}
<div {{ $attributes->class('overflow-hidden rounded-2xl border border-line') }}>
    <table class="w-full text-left text-sm" data-licenses>
        <thead class="bg-bg-alt text-ink max-sm:hidden">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Composant</th>
                <th scope="col" class="px-4 py-3 font-semibold">Version</th>
                <th scope="col" class="px-4 py-3 font-semibold">Licence</th>
                <th scope="col" class="px-4 py-3 font-semibold">Usage</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line max-sm:block">
            @foreach ($rows as $row)
                <tr class="max-sm:block max-sm:px-4 max-sm:py-3">
                    <th scope="row" class="font-medium text-ink max-sm:block sm:px-4 sm:py-3">{{ $row['name'] }}</th>
                    <td class="text-ink-muted max-sm:inline sm:px-4 sm:py-3 sm:whitespace-nowrap">{{ $row['version'] }}<span class="sm:hidden" aria-hidden="true"> · </span></td>
                    <td class="text-ink-soft max-sm:inline sm:px-4 sm:py-3">{{ $row['license'] }}</td>
                    <td class="text-ink-soft max-sm:mt-1 max-sm:block sm:px-4 sm:py-3">{{ $row['usage'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
