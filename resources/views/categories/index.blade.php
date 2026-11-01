@extends('layouts.app')

@section('title', 'Kategori Menu')
@section('breadcrumb', 'Ruang kerja / Katalog / Kategori Menu')
@section('page_title', 'Kategori Menu')

@section('header_actions')
    <a href="{{ route('categories.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Tambah Kategori</span>
    </a>
@endsection

@section('content')
    {{-- Page Intro & Summary Counters (Matches PDF Page 1) --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-teak">Racikan rasa, tertata rapi.</h2>
            <p class="text-sm text-taupe mt-0.5">
                Kelola kelompok menu untuk memudahkan pelayanan dan pemesanan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-sm text-taupe tabular-nums">
            <span><strong class="text-teak">{{ $categories->count() }}</strong> kategori</span>
            <span><strong class="text-teak">{{ $totalMenus }}</strong> menu</span>
            <x-status-badge tone="sage" :label="$activeCategories->count().' kategori aktif'" />
        </div>
    </div>

    {{-- Active Categories Grid (3 columns on desktop, Reusable <x-category-card>) --}}
    <div class="kn-stagger grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach ($activeCategories as $category)
            <x-category-card :category="$category" />
        @endforeach
    </div>

    {{-- Inactive / Seasonal Categories Banner Row (Matches PDF Page 1 bottom strip) --}}
    @if ($inactiveCategories->isNotEmpty())
        <div class="mt-5 space-y-3">
            @foreach ($inactiveCategories as $inactive)
                <div class="bg-white rounded-2xl border border-sand px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-xl bg-sand-soft text-taupe flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <a href="{{ route('categories.show', $inactive->slug) }}" class="text-base font-bold text-teak hover:text-copper">
                                    {{ $inactive->name }}
                                </a>
                                <x-status-badge tone="neutral" label="Tidak aktif" />
                            </div>
                            <p class="text-xs text-taupe mt-1">
                                {{ $inactive->menus_count }} menu · Tidak ditampilkan dalam pilihan pesanan baru.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('categories.edit', $inactive->slug) }}"
                       class="inline-flex items-center gap-1.5 text-sm font-semibold text-copper hover:text-copper-dark shrink-0">
                        <span>Kelola kategori</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <p class="mt-4 flex items-center gap-2 text-xs text-taupe">
        <svg class="w-4 h-4 text-taupe/70 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
        </svg>
        <span>
            Jumlah menu mencakup {{ $activeMenusCount }} menu reguler dan {{ $inactiveMenusCount }} menu dalam kategori tidak aktif.
        </span>
    </p>
@endsection
