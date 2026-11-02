@extends('layouts.app')

@php
    $canManageCatalog = ! isset($activeStaff) || ! $activeStaff || $activeStaff->canManageCatalog();
@endphp

@section('title', $canManageCatalog ? 'Daftar Menu' : 'Stok Harian Menu')
@section('breadcrumb', $canManageCatalog ? 'Beranda / Master Katalog / Daftar Menu' : 'Beranda / Katalog & Stok / Stok Harian Menu')
@section('page_title', $canManageCatalog ? 'Daftar Menu' : 'Stok Harian Menu')

@section('header_actions')
    @if ($canManageCatalog)
        <a href="{{ route('menus.create') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
            <span>Tambah Menu Baru</span>
        </a>
    @endif
@endsection

@section('content')
    {{-- Filter Toolbar --}}
    <form action="{{ route('menus.index') }}" method="GET" class="bg-white rounded-lg border border-sand p-3.5 mb-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3 shadow-2xs">
        <div class="flex flex-wrap items-center gap-2">
            <select name="category"
                    onchange="this.form.submit()"
                    class="rounded-md bg-alabaster border border-sand px-3 py-1.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Semua Kategori ({{ $totalCatalogCount }})</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                        {{ $cat->name }}{{ ! $cat->is_active ? ' (Nonaktif)' : '' }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                    onchange="this.form.submit()"
                    class="rounded-md bg-alabaster border border-sand px-3 py-1.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Semua Status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Habis</option>
            </select>

            <select name="featured"
                    onchange="this.form.submit()"
                    class="rounded-md bg-alabaster border border-sand px-3 py-1.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Semua Tipe</option>
                <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Menu Unggulan</option>
                <option value="no" {{ request('featured') === 'no' ? 'selected' : '' }}>Menu Reguler</option>
            </select>

            @if (request()->hasAny(['category', 'status', 'featured', 'search']))
                <a href="{{ route('menus.index') }}" class="text-xs font-semibold text-copper hover:underline px-2 py-1">
                    Reset
                </a>
            @endif
        </div>

        <div class="relative w-full lg:w-64">
            <svg class="w-3.5 h-3.5 text-taupe/70 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
            </svg>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Cari nama atau bahan menu..."
                   class="w-full pl-8 pr-3 py-1.5 rounded-md bg-alabaster border border-sand text-xs text-teak placeholder:text-taupe/65 focus:outline-none focus:bg-white focus:border-copper">
        </div>
    </form>

    {{-- Catalog Data Table --}}
    <div class="bg-white rounded-lg border border-sand overflow-hidden shadow-2xs">
        @if ($menus->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm font-bold text-teak">Tidak ada data menu yang cocok</p>
                <p class="text-xs text-taupe mt-1">Sesuaikan filter pencarian atau tambahkan menu baru.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-sand-soft/65 text-left text-[11px] uppercase tracking-wider font-bold text-taupe border-b border-sand">
                            <th class="pl-5 pr-3 py-3 w-20">Foto</th>
                            <th class="px-3 py-3">Nama Menu</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Stok Harian</th>
                            @if ($canManageCatalog)
                                <th class="pl-4 pr-5 py-3 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand/70">
                        @foreach ($menus as $menu)
                            <tr class="group hover:bg-alabaster/60 transition">
                                <td class="pl-5 pr-3 py-3 w-20">
                                    @if ($menu->image_url)
                                        <div class="w-12 h-12 rounded-md overflow-hidden border border-sand bg-sand-soft shrink-0">
                                            <img src="{{ $menu->image_url }}"
                                                 alt="{{ $menu->name }}"
                                                 loading="lazy"
                                                 decoding="async"
                                                 class="w-full h-full max-w-none object-cover {{ $menu->is_available ? '' : 'grayscale-[45%] opacity-75' }}">
                                        </div>
                                    @else
                                        <x-category-icon :slug="$menu->category?->slug ?? ''" />
                                    @endif
                                </td>

                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-teak">{{ $menu->name }}</span>
                                        @if ($menu->is_featured)
                                            <x-status-badge tone="brass" label="Unggulan" />
                                        @endif
                                    </div>
                                    @if ($menu->description)
                                        <p class="text-xs text-taupe truncate max-w-xs xl:max-w-md mt-0.5">{{ $menu->description }}</p>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-xs font-medium text-taupe">
                                    @if ($menu->category)
                                        @if ($canManageCatalog)
                                            <a href="{{ route('categories.show', $menu->category->slug) }}" class="hover:text-copper hover:underline">
                                                {{ $menu->category->name }}
                                            </a>
                                        @else
                                            <span>{{ $menu->category->name }}</span>
                                        @endif
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 font-bold text-teak tabular-nums">
                                    {{ $menu->formatted_price }}
                                </td>

                                <td class="px-4 py-3">
                                    <form action="{{ route('menus.toggle', $menu->slug) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="Klik untuk ubah status Tersedia / Habis" class="cursor-pointer">
                                            <x-status-badge
                                                :tone="$menu->is_available ? 'sage' : 'clay'"
                                                :label="$menu->is_available ? 'Tersedia' : 'Habis'" />
                                        </button>
                                    </form>
                                </td>

                                @if ($canManageCatalog)
                                    <td class="pl-4 pr-5 py-3 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('menus.edit', $menu->slug) }}"
                                               class="px-2.5 py-1 rounded border border-sand text-xs font-semibold text-teak hover:border-copper hover:text-copper transition">
                                                Edit
                                            </a>
                                            <form action="{{ route('menus.destroy', $menu->slug) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Arsipkan menu {{ $menu->name }} dari katalog?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        title="Arsipkan menu"
                                                        class="p-1.5 rounded text-taupe/70 hover:text-clay hover:bg-clay-tint transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="px-5 py-3.5 bg-alabaster/40 border-t border-sand flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <p class="text-xs text-taupe tabular-nums">
                    Menampilkan {{ $menus->firstItem() }}–{{ $menus->lastItem() }} dari {{ $menus->total() }} item menu
                </p>

                @if ($menus->hasPages())
                    <div class="flex flex-wrap items-center gap-1">
                        @if ($menus->onFirstPage())
                            <span class="px-2.5 py-1 rounded border border-sand/70 text-xs text-taupe/45 cursor-not-allowed">‹ Sebelumnya</span>
                        @else
                            <a href="{{ $menus->previousPageUrl() }}" class="px-2.5 py-1 rounded border border-sand bg-white text-xs font-semibold text-teak hover:border-copper transition">‹ Sebelumnya</a>
                        @endif

                        @foreach ($menus->getUrlRange(1, $menus->lastPage()) as $page => $url)
                            @if ($page == $menus->currentPage())
                                <span class="w-7 h-7 rounded bg-teak text-white text-xs font-bold flex items-center justify-center tabular-nums">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="w-7 h-7 rounded border border-sand bg-white text-xs font-semibold text-teak hover:border-copper flex items-center justify-center tabular-nums transition">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($menus->hasMorePages())
                            <a href="{{ $menus->nextPageUrl() }}" class="px-2.5 py-1 rounded border border-sand bg-white text-xs font-semibold text-teak hover:border-copper transition">Berikutnya ›</a>
                        @else
                            <span class="px-2.5 py-1 rounded border border-sand/70 text-xs text-taupe/45 cursor-not-allowed">Berikutnya ›</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
