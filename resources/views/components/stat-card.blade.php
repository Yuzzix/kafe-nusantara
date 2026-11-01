@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'copper',
])

@php
    $accentStyles = match ($tone) {
        'sage' => 'bg-sage-tint text-sage',
        'clay' => 'bg-clay-tint text-clay',
        'saffron' => 'bg-saffron-tint text-saffron',
        'brass' => 'bg-brass-tint text-[#8C6538]',
        default => 'bg-copper-tint text-copper',
    };
@endphp

<div {{ $attributes->merge(['class' => 'kn-card-lift bg-white rounded-2xl border border-sand p-5 flex items-center gap-4']) }}>
    @isset($icon)
        <div class="w-12 h-12 rounded-xl {{ $accentStyles }} flex items-center justify-center shrink-0">
            {{ $icon }}
        </div>
    @endisset

    <div class="min-w-0">
        <p class="text-xs font-medium text-taupe">{{ $label }}</p>
        <p class="text-xl lg:text-2xl font-bold text-teak tabular-nums mt-0.5 truncate">{{ $value }}</p>
        @if ($hint)
            <p class="text-xs text-taupe mt-1">{{ $hint }}</p>
        @endif
    </div>
</div>
