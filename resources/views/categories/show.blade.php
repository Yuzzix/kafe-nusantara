@extends('layouts.app')

@section('title', 'Detail Kategori — '.$category->name)
@section('breadcrumb')
    <a href="{{ route('categories.index') }}" class="hover:text-copper">Ruang kerja / Kategori Menu</a> / {{ $category->name }}
@endsection
@section('page_title', 'Detail Kategori')

@section('header_actions')
    <a href="{{ route('menus.create', ['category' => $category->slug]) }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Tambah Menu</span>
    </a>
@endsection

@section('content')
    {{-- Dark Smoked-Teak Category Banner (Matches PDF Page 2) --}}
    <div class="rounded-2xl bg-teak text-alabaster px-6 sm:px-8 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-category-icon :slug="$category->slug" :icon="$category->icon" size="lg" :dark="true" />
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-white">{{ $category->name }}</h2>
                    <x-status-badge :tone="$category->is_active ? 'sage' : 'neutral'" :label="$category->is_active ? 'Aktif' : 'Tidak aktif'" />
                </div>
                <p class="text-sm text-alabaster/70 mt-1">
                    {{ $category->description ?: 'Daftar lengkap sajian dalam kategori ini.' }}
                </p>
            </div>
        </div>

        <div class="sm:text-right shrink-0">
            <p class="text-2xl font-bold text-brass tabular-nums">{{ $category->menus->count() }} menu</p>
            <p class="text-xs text-alabaster/65 mt-0.5">
                @if ($category->menus->isEmpty())
                    Belum ada sajian terdaftar
                @elseif ($availableCount === $category->menus->count())
                    Seluruhnya tersedia
                @else
                    {{ $availableCount }} tersedia · {{ $category->menus->count() - $availableCount }} habis
                @endif
            </p>
        </div>
    </div>

    {{-- Back Link & Edit Category Button --}}
    <div class="flex items-center justify-between gap-4 my-5">
        <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-taupe hover:text-teak transition">
            <span>←</span>
            <span>Semua kategori</span>
        </a>

        <a href="{{ route('categories.edit', $category->slug) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-sand text-xs font-semibold text-teak hover:border-copper transition">
            <svg class="w-3.5 h-3.5 text-taupe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
            </svg>
            <span>Ubah Kategori</span>
        </a>
    </div>

    {{-- Child Menus Grid + "Lengkapi foto menu" Card (Matches PDF Page 2) --}}
    @if ($category->menus->isEmpty())
        <div class="bg-white rounded-2xl border border-sand p-10 text-center">
            <p class="text-base font-bold text-teak">Belum ada menu di kategori {{ $category->name }}</p>
            <p class="text-xs text-taupe mt-1 mb-5">Tambahkan sajian pertama untuk mulai menampilkannya kepada staf kasir.</p>
            <a href="{{ route('menus.create', ['category' => $category->slug]) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold">
                + Tambah Menu Pertama
            </a>
        </div>
    @else
        <div class="kn-stagger grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($category->menus as $menu)
                <x-menu-card :menu="$menu" />
            @endforeach

            @if ($missingPhotoCount > 0)
                <div class="rounded-2xl bg-sand-soft/75 border border-sand border-dashed p-5 flex flex-col justify-center">
                    <div class="w-10 h-10 rounded-xl bg-white border border-sand flex items-center justify-center text-brass mb-3">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-teak">Lengkapi foto menu</h3>
                    <p class="text-xs text-taupe mt-1.5 leading-relaxed">
                        Foto belum tersedia pada {{ $missingPhotoCount }} menu. Unggah melalui ikon pensil di kartu menu agar staf lebih mudah mengenal setiap racikan.
                    </p>
                </div>
            @endif
        </div>
    @endif
@endsection
