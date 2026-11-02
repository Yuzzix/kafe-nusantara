@extends('layouts.app')

@section('title', 'Detail Kategori — '.$category->name)
@section('breadcrumb')
    <a href="{{ route('categories.index') }}" class="hover:text-copper">Beranda / Kategori Menu</a> / {{ $category->name }}
@endsection
@section('page_title', $category->name)

@section('header_actions')
    <a href="{{ route('categories.edit', $category->slug) }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-sand text-xs font-semibold text-teak hover:border-copper transition">
        <span>Edit Kategori</span>
    </a>
    <a href="{{ route('menus.create', ['category' => $category->slug]) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Tambah Menu</span>
    </a>
@endsection

@section('content')
    {{-- Structured Master Record Summary Card with Light Floral Batik Watermark --}}
    <div class="kn-batik-card-light rounded-lg border border-sand border-l-[3px] border-l-copper p-5 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
        <div class="flex items-start gap-3.5">
            <x-category-icon :slug="$category->slug" :icon="$category->icon" size="lg" />
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-base font-bold text-teak">{{ $category->name }}</h2>
                    <x-status-badge :tone="$category->is_active ? 'sage' : 'neutral'" :label="$category->is_active ? 'Kategori Aktif' : 'Tidak aktif'" />
                    <span class="text-xs text-taupe tabular-nums">· Urutan #{{ $category->sort_order }}</span>
                </div>
                <p class="text-xs text-taupe mt-1">
                    {{ $category->description ?: 'Daftar menu yang terdaftar di bawah kategori ini.' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-5 sm:text-right shrink-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-sand/60 tabular-nums">
            <div>
                <p class="text-lg font-bold text-teak">{{ $category->menus->count() }} menu</p>
                <p class="text-[11px] text-taupe">
                    @if ($category->menus->isEmpty())
                        Belum ada item
                    @elseif ($availableCount === $category->menus->count())
                        Semua tersedia
                    @else
                        {{ $availableCount }} tersedia · {{ $category->menus->count() - $availableCount }} habis
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-taupe hover:text-copper transition">
            <span>← Kembali ke daftar kategori</span>
        </a>
    </div>

    {{-- Child Menus Grid --}}
    @if ($category->menus->isEmpty())
        <div class="bg-white rounded-lg border border-sand p-8 text-center">
            <p class="text-sm font-bold text-teak">Belum ada menu pada kategori {{ $category->name }}</p>
            <p class="text-xs text-taupe mt-1 mb-4">Tambahkan item menu baru untuk menghubungkannya ke kategori ini.</p>
            <a href="{{ route('menus.create', ['category' => $category->slug]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold">
                + Tambah Menu Pertama
            </a>
        </div>
    @else
        <div class="kn-stagger grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($category->menus as $menu)
                <x-menu-card :menu="$menu" />
            @endforeach
        </div>
    @endif
@endsection
