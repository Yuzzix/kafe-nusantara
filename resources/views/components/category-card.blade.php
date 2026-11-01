@props(['category'])

<a href="{{ route('categories.show', $category->slug) }}"
   class="kn-card-lift group bg-white rounded-2xl border border-sand p-6 flex flex-col justify-between hover:border-copper/50">
    <div>
        <div class="flex items-start justify-between gap-3 mb-5">
            <x-category-icon :slug="$category->slug" :icon="$category->icon" />
            <x-status-badge :tone="$category->is_active ? 'sage' : 'neutral'" :label="$category->is_active ? 'Aktif' : 'Tidak aktif'" />
        </div>

        <h3 class="text-lg font-bold text-teak group-hover:text-copper transition">
            {{ $category->name }}
        </h3>
        <p class="text-sm text-taupe mt-1.5 leading-relaxed line-clamp-2">
            {{ $category->description ?: 'Kelompok sajian pilihan Kafe Nusantara.' }}
        </p>
    </div>

    <div class="mt-6 pt-4 border-t border-sand/70 flex items-center justify-between text-sm">
        <span class="font-semibold text-teak tabular-nums">{{ $category->menus_count }} menu</span>
        <span class="inline-flex items-center gap-1 font-semibold text-copper group-hover:translate-x-0.5 transition">
            Lihat menu
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10"/>
            </svg>
        </span>
    </div>
</a>
