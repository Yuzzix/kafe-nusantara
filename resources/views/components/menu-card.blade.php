@props(['menu'])

<div class="kn-card-lift bg-white rounded-2xl border border-sand p-4 flex flex-col justify-between hover:border-copper/40">
    <div>
        {{-- Photo or Warm Placeholder --}}
        <div class="relative h-32 rounded-xl bg-sand-soft/80 overflow-hidden flex items-center justify-center mb-4">
            @if ($menu->is_featured)
                <div class="absolute top-2.5 left-2.5 z-10">
                    <x-status-badge tone="brass" label="Best Seller" />
                </div>
            @endif

            @if ($menu->image)
                <img src="{{ asset('storage/'.$menu->image) }}"
                     alt="{{ $menu->name }}"
                     class="w-full h-full object-cover">
            @else
                <div class="text-brass/75">
                    <x-category-icon :slug="$menu->category?->slug ?? ''" class="!bg-transparent !border-0 !text-brass" />
                </div>
            @endif
        </div>

        <div class="flex items-start justify-between gap-2">
            <h3 class="text-[15px] font-bold text-teak leading-snug">{{ $menu->name }}</h3>
            <a href="{{ route('menus.edit', $menu->slug) }}"
               title="Ubah menu"
               class="text-taupe/60 hover:text-copper p-1 rounded-lg transition shrink-0">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                </svg>
            </a>
        </div>

        <p class="text-xs text-taupe mt-1.5 leading-relaxed line-clamp-2">
            {{ $menu->description ?: 'Racikan khas Kafe Nusantara.' }}
        </p>
    </div>

    <div class="mt-5 pt-3 border-t border-sand/60 flex items-center justify-between gap-2">
        <span class="text-base font-bold text-copper tabular-nums">
            {{ $menu->formatted_price }}
        </span>
        <x-status-badge :tone="$menu->is_available ? 'sage' : 'clay'" :label="$menu->is_available ? 'Tersedia' : 'Habis'" />
    </div>
</div>
