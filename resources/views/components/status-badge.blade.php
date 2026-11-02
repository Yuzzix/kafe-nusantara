@props([
    'tone' => 'neutral',
    'label' => '',
])

@php
    $badgeClass = match ($tone) {
        'sage', 'success', 'available', 'completed', 'paid', 'served' => 'bg-sage-tint text-sage border-sage/25',
        'saffron', 'warning', 'preparing', 'pending', 'reserved', 'unpaid' => 'bg-saffron-tint text-saffron border-saffron/25',
        'clay', 'danger', 'occupied', 'cancelled', 'unavailable' => 'bg-clay-tint text-clay border-clay/25',
        'brass', 'featured' => 'bg-brass-tint text-[#7C582C] border-brass/35',
        default => 'bg-sand-soft text-taupe border-sand',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold tracking-tight border {$badgeClass}"]) }}>
    {{ $label ?: $slot }}
</span>
