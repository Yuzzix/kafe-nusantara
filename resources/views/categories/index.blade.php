@extends('layouts.app')

@section('title', 'Kategori Menu')
@section('breadcrumb', 'Beranda / Master Katalog / Kategori Menu')
@section('page_title', 'Kategori Menu')

@section('header_actions')
    <a href="{{ route('categories.create') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Tambah Kategori</span>
    </a>
@endsection

@section('content')
    {{-- Practical Summary Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5 pb-3.5 border-b border-sand text-xs text-taupe">
        <span>Daftar pengelompokan menu aktif pada modul kasir dan buku menu.</span>
        <div class="flex flex-wrap items-center gap-3 tabular-nums">
            <span><strong class="text-teak">{{ $categories->count() }}</strong> kategori</span>
            <span class="text-sand">|</span>
            <span><strong class="text-teak">{{ $totalMenus }}</strong> total menu</span>
            <x-status-badge tone="sage" :label="$activeCategories->count().' aktif'" />
        </div>
    </div>

    {{-- Active Categories Grid --}}
    <div class="kn-stagger grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach ($activeCategories as $category)
            <x-category-card :category="$category" />
        @endforeach
    </div>

    {{-- Inactive / Seasonal Categories Section --}}
    @if ($inactiveCategories->isNotEmpty())
        <div class="mt-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-taupe mb-2.5">Kategori Nonaktif / Musiman</h2>
            <div class="space-y-2.5">
                @foreach ($inactiveCategories as $inactive)
                    <div class="bg-white rounded-lg border border-sand px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <x-category-icon :slug="$inactive->slug" :icon="$inactive->icon" />
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('categories.show', $inactive->slug) }}" class="text-sm font-bold text-teak hover:text-copper">
                                        {{ $inactive->name }}
                                    </a>
                                    <x-status-badge tone="neutral" label="Tidak aktif" />
                                </div>
                                <p class="text-xs text-taupe mt-0.5">
                                    {{ $inactive->menus_count }} item menu · Disembunyikan dari form kasir.
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('categories.edit', $inactive->slug) }}"
                           class="px-3 py-1.5 rounded-md border border-sand text-xs font-semibold text-teak hover:border-copper hover:text-copper transition shrink-0">
                            Ubah Status
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
