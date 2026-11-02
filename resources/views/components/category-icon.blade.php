@props([
    'slug' => '',
    'icon' => null,
    'size' => 'md',
    'dark' => false,
])

@php
    $boxClass = match ($size) {
        'sm' => 'w-9 h-9 rounded-md',
        'lg' => 'w-12 h-12 rounded-lg',
        default => 'w-10 h-10 rounded-lg',
    };

    $surfaceClass = $dark
        ? 'bg-white/10 text-brass border border-white/15'
        : 'bg-sand-soft text-teak border border-sand';
@endphp

<div {{ $attributes->merge(['class' => "{$boxClass} {$surfaceClass} flex items-center justify-center shrink-0"]) }}>
    @switch($slug)
        @case('makanan-utama')
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>
            </svg>
            @break

        @case('camilan')
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/><path d="M4.5 15.5h15"/><path d="m5 11 4-7"/><path d="m9 11 1 9"/>
            </svg>
            @break

        @case('kopi')
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v7a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/>
            </svg>
            @break

        @case('non-kopi')
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="m6 8 1.75 12.28a2 2 0 0 0 2 1.72h4.54a2 2 0 0 0 2-1.72L18 8"/><path d="M5 8h14"/><path d="M7 15a6.47 6.47 0 0 1 5 0 6.47 6.47 0 0 0 5 0"/><path d="m12 8 1-6h2"/>
            </svg>
            @break

        @case('teh-minuman-segar')
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8 2h8l-1 18a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2Z"/><path d="M7 7h10"/>
            </svg>
            @break

        @case('dessert')
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/><path d="M2 21h20"/><path d="M7 8v3"/><path d="M12 8v3"/><path d="M17 8v3"/><path d="M7 4h.01"/><path d="M12 4h.01"/><path d="M17 4h.01"/>
            </svg>
            @break

        @default
            @if ($icon)
                <span class="text-sm leading-none">{{ $icon }}</span>
            @else
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Z"/><path d="M12 8v8"/><path d="M8 12h8"/>
                </svg>
            @endif
    @endswitch
</div>
