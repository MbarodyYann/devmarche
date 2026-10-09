<x-layouts.app title="À propos" description="DevMarché, par l'équipe Baobab Stack (Sénégal) : le projet, l'équipe, la stack, les licences et l'usage de l'intelligence artificielle.">
    {{-- Page « À propos ». Données : config/credits.php et les fichiers de verrouillage. --}}
    <x-page-intro eyebrow="À propos" title="Les devs d'Afrique francophone méritaient leur propre place de marché.">
        Missions payées en FCFA, questions-réponses en français et annuaire de devs : une seule plateforme, pensée pour la 3G et le Mobile Money.
    </x-page-intro>

    <div class="mx-auto mt-14 max-w-3xl space-y-14 sm:mt-20 sm:space-y-20">
        {{-- Le projet et la mission --}}
        <section aria-labelledby="projet-titre">
            <h2 id="projet-titre" class="text-[28px] sm:text-[36px]">Le projet</h2>
            <div class="mt-4 space-y-4 text-lg text-ink-soft">
                <p>Aujourd'hui, les missions passent par des groupes WhatsApp : elles s'y perdent en quelques heures, et rien ne garde la trace de ce qu'un dev a déjà livré. Les plateformes internationales, elles, sont en anglais, en dollars, et ne connaissent ni Wave ni Orange Money.</p>
                <p>DevMarché réunit trois choses au même endroit : des missions avec un budget en FCFA, des questions-réponses en français qui restent trouvables, et un annuaire où les cauris montrent qui a déjà livré et aidé la communauté.</p>
            </div>
        </section>

        {{-- L'équipe --}}
        <section id="equipe" aria-labelledby="equipe-titre" class="scroll-mt-28">
            <h2 id="equipe-titre" class="text-[28px] sm:text-[36px]">L'équipe Baobab Stack</h2>
            <p class="mt-4 text-lg text-ink-soft">Équipe du Sénégal, au concours CADEV 2026 organisé par Systalink.</p>
            <ul class="mt-6 grid gap-4 sm:grid-cols-2">
                @foreach (config('credits.team') as $member)
                    <li class="min-w-0">
                        <x-card padding="p-5" class="flex items-center gap-4">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary-50 font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-100" aria-hidden="true">
                                {{ collect(explode(' ', $member['name']))->map(fn ($part) => mb_substr($part, 0, 1))->join('') }}
                            </span>
                            <span class="min-w-0">
                                <span class="block font-serif text-xl font-medium text-ink">{{ $member['name'] }}</span>
                                <span class="block text-sm text-ink-muted">{{ $member['role'] }}</span>
                            </span>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- La stack --}}
        <section aria-labelledby="stack-titre">
            <h2 id="stack-titre" class="text-[28px] sm:text-[36px]">La stack</h2>
            <dl class="mt-6 divide-y divide-line border-y border-line">
                @foreach (config('credits.stack') as $item)
                    <div class="grid gap-1 py-4 sm:grid-cols-[14rem_1fr] sm:gap-6">
                        <dt class="font-semibold text-ink">{{ $item['name'] }}</dt>
                        <dd class="text-ink-soft">{{ $item['role'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Licences : générées depuis composer.lock, package-lock.json et config/credits.php --}}
        <section id="licences" aria-labelledby="licences-titre" class="scroll-mt-28">
            <h2 id="licences-titre" class="text-[28px] sm:text-[36px]">Licences</h2>
            {{-- Sur mobile, chaque ligne devient un bloc empilé (pas de défilement horizontal à 360 px). --}}
            <div class="mt-6 overflow-hidden rounded-2xl border border-line">
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
                        @foreach (App\Support\Credits::licenses() as $row)
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
        </section>

        {{-- Marques de tiers citées sur le site (« Pensé pour ») --}}
        <section id="marques" aria-labelledby="marques-titre" class="scroll-mt-28">
            <h2 id="marques-titre" class="text-[28px] sm:text-[36px]">Marques citées</h2>
            <p class="mt-4 text-lg text-ink-soft" data-trademarks>Wave, Orange Money et MTN MoMo sont des marques de leurs propriétaires respectifs. DevMarché n'est affilié à aucune de ces sociétés ; le paiement est simulé dans cette version.</p>
        </section>

        {{-- Déclaration d'usage de l'IA (article 6) --}}
        <section id="ia" aria-labelledby="ia-titre" class="scroll-mt-28">
            <h2 id="ia-titre" class="text-[28px] sm:text-[36px]">Usage de l'intelligence artificielle</h2>
            <ul class="mt-6 space-y-3 text-lg text-ink-soft">
                @foreach (config('credits.ai') as $item)
                    <li><strong class="font-semibold text-ink">{{ $item['tool'] }}</strong> : {{ $item['parts'] }}</li>
                @endforeach
            </ul>
        </section>
    </div>

</x-layouts.app>
