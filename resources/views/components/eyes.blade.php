@props(['size' => 'md'])

@php($uid = 'oeil-'.Str::random(6))

{{-- Les yeux du car rapide : deux amandes peintes. Les pupilles suivent le curseur,
     sauf si l'utilisateur préfère réduire les animations. --}}
<div {{ $attributes->merge(['class' => match ($size) {
        'sm' => 'w-10',
        'lg' => 'w-56 sm:w-72',
        default => 'w-36',
    }]) }}
     aria-hidden="true"
     x-data="{
        x: 0, y: 0,
        still: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        track(e) {
            if (this.still) return;
            const r = this.$el.getBoundingClientRect();
            const dx = e.clientX - (r.left + r.width / 2), dy = e.clientY - (r.top + r.height / 2);
            const d = Math.hypot(dx, dy) || 1, m = Math.min(1, d / 240);
            this.x = dx / d * 6 * m; this.y = dy / d * 3.5 * m;
        },
     }"
     x-on:mousemove.window.throttle.30ms="track($event)">
    <svg viewBox="0 0 120 44" class="block h-auto w-full" xmlns="http://www.w3.org/2000/svg">
        @foreach ([30, 90] as $cx)
            <g>
                {{-- cils --}}
                <path class="stroke-ink" stroke-width="2.5" stroke-linecap="round" fill="none"
                      d="M{{ $cx - 18 }} 12l-4-6M{{ $cx - 8 }} 7l-2-6M{{ $cx + 3 }} 6l1-6M{{ $cx + 13 }} 9l4-5"/>
                @php($almond = 'M'.($cx - 25).' 26C'.($cx - 15).' 9 '.($cx + 15).' 9 '.($cx + 25).' 26C'.($cx + 15).' 40 '.($cx - 15).' 40 '.($cx - 25).' 26Z')
                <clipPath id="{{ $uid }}-{{ $cx }}"><path d="{{ $almond }}"/></clipPath>
                {{-- amande --}}
                <path class="fill-white" d="{{ $almond }}"/>
                {{-- iris et pupille, découpés par l'amande --}}
                <g clip-path="url(#{{ $uid }}-{{ $cx }})">
                    <g x-bind:transform="`translate(${x} ${y})`">
                        <circle class="fill-secondary" cx="{{ $cx }}" cy="25" r="9"/>
                        <circle class="fill-shadow" cx="{{ $cx }}" cy="25" r="4"/>
                        <circle class="fill-white" cx="{{ $cx + 3 }}" cy="21.5" r="1.8"/>
                    </g>
                </g>
                <path class="stroke-ink" fill="none" stroke-width="3" stroke-linejoin="round" d="{{ $almond }}"/>
            </g>
        @endforeach
    </svg>
</div>
