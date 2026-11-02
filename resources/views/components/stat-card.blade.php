@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'copper',
])

@php
    $borderAccent = match ($tone) {
        'sage' => 'border-l-sage',
        'clay' => 'border-l-clay',
        'saffron' => 'border-l-saffron',
        'brass' => 'border-l-brass',
        default => 'border-l-copper',
    };
    $iconTint = match ($tone) {
        'sage' => 'text-sage',
        'clay' => 'text-clay',
        'saffron' => 'text-saffron',
        'brass' => 'text-[#8C6538]',
        default => 'text-copper',
    };
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-lg border border-sand border-l-[3px] {$borderAccent} p-4 flex flex-col justify-between shadow-2xs"]) }}>
    <div class="flex items-start justify-between gap-2">
        <p class="text-[11px] font-bold uppercase tracking-wider text-taupe">{{ $label }}</p>
        @isset($icon)
            <div class="w-4 h-4 {{ $iconTint }} opacity-80 shrink-0 [&>svg]:w-4 [&>svg]:h-4">
                {{ $icon }}
            </div>
        @endisset
    </div>

    <p class="text-xl lg:text-2xl font-bold text-teak tabular-nums mt-2 truncate">{{ $value }}</p>

    @if ($hint)
        <p class="text-[11.5px] text-taupe mt-1.5 pt-1.5 border-t border-sand/60 truncate">{{ $hint }}</p>
    @endif
</div>
