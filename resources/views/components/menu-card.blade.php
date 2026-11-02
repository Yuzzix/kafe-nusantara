@props(['menu'])

<div class="group kn-card-lift bg-white rounded-lg border border-sand overflow-hidden flex flex-col justify-between hover:border-copper/60 shadow-2xs">
    <div>
        {{-- Edge-to-edge Top Photo Bleed (Crafted Card Layout) --}}
        <div class="relative h-36 bg-sand-soft border-b border-sand overflow-hidden flex items-center justify-center">
            @if ($menu->is_featured)
                <div class="absolute top-2.5 left-2.5 z-10">
                    <x-status-badge tone="brass" label="Unggulan" />
                </div>
            @endif

            @if ($menu->image_url)
                <img src="{{ $menu->image_url }}"
                     alt="{{ $menu->name }}"
                     loading="lazy"
                     decoding="async"
                     class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105 {{ $menu->is_available ? '' : 'grayscale-[45%] opacity-75' }}">
            @else
                <div class="text-taupe/60">
                    <x-category-icon :slug="$menu->category?->slug ?? ''" class="!bg-transparent !border-0" />
                </div>
            @endif
        </div>

        <div class="p-4 pb-2">
            <div class="flex items-start justify-between gap-2">
                <h3 class="text-sm font-bold text-teak leading-snug">{{ $menu->name }}</h3>
                <a href="{{ route('menus.edit', $menu->slug) }}"
                   title="Edit menu"
                   class="text-taupe/70 hover:text-copper p-1 -mr-1 -mt-0.5 rounded transition shrink-0">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                    </svg>
                </a>
            </div>

            <p class="text-xs text-taupe mt-1 leading-relaxed line-clamp-2">
                {{ $menu->description ?: 'Menu reguler Kafe Nusantara.' }}
            </p>
        </div>
    </div>

    <div class="px-4 py-3 mt-2 bg-alabaster/60 border-t border-sand/70 flex items-center justify-between gap-2">
        <span class="text-sm font-bold text-copper tabular-nums">
            {{ $menu->formatted_price }}
        </span>
        <x-status-badge :tone="$menu->is_available ? 'sage' : 'clay'" :label="$menu->is_available ? 'Tersedia' : 'Habis'" />
    </div>
</div>
