@props([
    'tone' => 'neutral',
    'label' => '',
])

@php
    $styles = match ($tone) {
        'sage', 'success', 'available', 'completed', 'paid', 'served' => [
            'pill' => 'bg-sage-tint text-sage border-sage/15',
            'dot' => 'bg-sage',
        ],
        'saffron', 'warning', 'preparing', 'pending', 'reserved', 'unpaid' => [
            'pill' => 'bg-saffron-tint text-saffron border-saffron/15',
            'dot' => 'bg-saffron',
        ],
        'clay', 'danger', 'occupied', 'cancelled', 'unavailable' => [
            'pill' => 'bg-clay-tint text-clay border-clay/15',
            'dot' => 'bg-clay',
        ],
        'brass', 'featured' => [
            'pill' => 'bg-brass-tint text-[#8C6538] border-brass/30',
            'dot' => 'bg-brass',
        ],
        default => [
            'pill' => 'bg-sand-soft text-taupe border-sand',
            'dot' => 'bg-taupe/70',
        ],
    };
    $isLiveTone = in_array($tone, ['saffron', 'warning', 'preparing', 'pending'], true);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border {$styles['pill']}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $styles['dot'] }} {{ $isLiveTone ? 'kn-pulse-dot' : '' }}"></span>
    <span>{{ $label ?: $slot }}</span>
</span>
