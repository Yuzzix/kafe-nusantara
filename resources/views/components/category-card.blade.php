@props(['category'])

<a href="{{ route('categories.show', $category->slug) }}"
   class="kn-card-lift group bg-white rounded-lg border border-sand hover:border-copper/60 p-5 flex flex-col justify-between shadow-2xs">
    <div>
        <div class="flex items-center justify-between gap-3 mb-3.5">
            <div class="flex items-center gap-3 min-w-0">
                <x-category-icon :slug="$category->slug" :icon="$category->icon" />
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-teak group-hover:text-copper transition truncate">
                        {{ $category->name }}
                    </h3>
                    <p class="text-[11px] font-medium text-taupe tabular-nums">Urutan #{{ $category->sort_order }}</p>
                </div>
            </div>
            <x-status-badge :tone="$category->is_active ? 'sage' : 'neutral'" :label="$category->is_active ? 'Aktif' : 'Nonaktif'" />
        </div>

        <p class="text-xs text-taupe leading-relaxed line-clamp-2">
            {{ $category->description ?: 'Daftar menu dalam kategori ini.' }}
        </p>
    </div>

    <div class="mt-4 pt-3 border-t border-sand/70 flex items-center justify-between text-xs">
        <span class="font-bold text-teak tabular-nums">{{ $category->menus_count }} item menu</span>
        <span class="font-semibold text-copper group-hover:underline">
            Buka rincian →
        </span>
    </div>
</a>
