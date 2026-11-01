@extends('layouts.app')

@section('title', 'Daftar Menu')
@section('breadcrumb', 'Ruang kerja / Katalog / Daftar Menu')
@section('page_title', 'Daftar Menu')

@section('header_actions')
    <a href="{{ route('menus.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Tambah Menu Baru</span>
    </a>
@endsection

@section('content')
    {{-- Page Intro (Matches PDF Page 3) --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-5">
        <div>
            <h2 class="text-xl font-bold text-teak">Katalog cita rasa Nusantara</h2>
            <p class="text-sm text-taupe mt-0.5">
                Atur harga, menu unggulan, dan ketersediaan sajian dari satu tempat.
            </p>
        </div>

        <x-status-badge tone="sage" :label="$totalCatalogCount.' menu dalam katalog'" />
    </div>

    {{-- Filter Bar (Matches PDF Page 3) --}}
    <form action="{{ route('menus.index') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2.5">
            <select name="category"
                    onchange="this.form.submit()"
                    class="rounded-xl bg-white border border-sand px-3.5 py-2.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                        {{ $cat->name }}{{ ! $cat->is_active ? ' (Nonaktif)' : '' }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                    onchange="this.form.submit()"
                    class="rounded-xl bg-white border border-sand px-3.5 py-2.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Semua status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Habis</option>
            </select>

            <select name="featured"
                    onchange="this.form.submit()"
                    class="rounded-xl bg-white border border-sand px-3.5 py-2.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Menu unggulan</option>
                <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Best Seller saja</option>
                <option value="no" {{ request('featured') === 'no' ? 'selected' : '' }}>Reguler saja</option>
            </select>

            @if (request()->hasAny(['category', 'status', 'featured', 'search']))
                <a href="{{ route('menus.index') }}" class="text-xs font-semibold text-copper hover:underline px-2 py-1">
                    Reset filter
                </a>
            @endif
        </div>

        <div class="relative w-full lg:w-72">
            <svg class="w-4 h-4 text-taupe/70 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
            </svg>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Cari nama menu..."
                   class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-sand text-xs text-teak placeholder:text-taupe/65 focus:outline-none focus:border-copper">
        </div>
    </form>

    {{-- Catalog Table Card (Matches PDF Page 3) --}}
    <div class="bg-white rounded-2xl border border-sand overflow-hidden">
        @if ($menus->isEmpty())
            <div class="p-12 text-center">
                <p class="text-base font-bold text-teak">Tidak ada menu yang cocok dengan filter</p>
                <p class="text-xs text-taupe mt-1">Ubah pilihan kategori/status atau tambahkan menu baru.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-alabaster/70 text-left text-xs font-semibold text-taupe border-b border-sand">
                            <th class="pl-6 pr-3 py-3.5 w-16">Foto</th>
                            <th class="px-3 py-3.5">Nama Menu</th>
                            <th class="px-4 py-3.5">Kategori</th>
                            <th class="px-4 py-3.5">Harga</th>
                            <th class="px-4 py-3.5">Ketersediaan</th>
                            <th class="pl-4 pr-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand/70">
                        @foreach ($menus as $menu)
                            <tr class="hover:bg-alabaster/55 transition">
                                {{-- Thumbnail or Category Icon --}}
                                <td class="pl-6 pr-3 py-3.5">
                                    @if ($menu->image)
                                        <img src="{{ asset('storage/'.$menu->image) }}"
                                             alt="{{ $menu->name }}"
                                             class="w-11 h-11 rounded-xl object-cover border border-sand">
                                    @else
                                        <x-category-icon :slug="$menu->category?->slug ?? ''" size="sm" />
                                    @endif
                                </td>

                                {{-- Name & Best Seller Pill --}}
                                <td class="px-3 py-3.5">
                                    <div class="font-bold text-teak">{{ $menu->name }}</div>
                                    @if ($menu->is_featured)
                                        <div class="mt-1">
                                            <x-status-badge tone="brass" label="Best Seller" />
                                        </div>
                                    @elseif ($menu->description)
                                        <p class="text-xs text-taupe truncate max-w-md mt-0.5">{{ $menu->description }}</p>
                                    @endif
                                </td>

                                {{-- Category Link --}}
                                <td class="px-4 py-3.5 text-taupe">
                                    @if ($menu->category)
                                        <a href="{{ route('categories.show', $menu->category->slug) }}" class="hover:text-copper">
                                            {{ $menu->category->name }}
                                        </a>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>

                                {{-- Price --}}
                                <td class="px-4 py-3.5 font-bold text-teak tabular-nums">
                                    {{ $menu->formatted_price }}
                                </td>

                                {{-- Availability Toggle Pill --}}
                                <td class="px-4 py-3.5">
                                    <form action="{{ route('menus.toggle', $menu->slug) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" title="Klik untuk mengubah status Tersedia / Habis" class="cursor-pointer">
                                            <x-status-badge
                                                :tone="$menu->is_available ? 'sage' : 'clay'"
                                                :label="$menu->is_available ? 'Tersedia' : 'Habis'" />
                                        </button>
                                    </form>
                                </td>

                                {{-- Actions --}}
                                <td class="pl-4 pr-6 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('menus.edit', $menu->slug) }}"
                                           class="px-2.5 py-1.5 rounded-lg border border-sand text-xs font-semibold text-teak hover:border-copper hover:text-copper transition">
                                            Ubah
                                        </a>
                                        <form action="{{ route('menus.destroy', $menu->slug) }}"
                                              method="POST"
                                              onsubmit="return confirm('Arsipkan menu {{ $menu->name }} dari katalog?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Arsipkan menu"
                                                    class="p-1.5 rounded-lg text-taupe/70 hover:text-clay hover:bg-clay-tint transition">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Custom Footer Pagination (Matches PDF Page 3: "Menampilkan 1–8 dari 36 menu" + Sebelumnya 1 2 3 ... Berikutnya) --}}
            <div class="px-6 py-4 border-t border-sand flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <p class="text-xs text-taupe tabular-nums">
                    Menampilkan {{ $menus->firstItem() }}–{{ $menus->lastItem() }} dari {{ $menus->total() }} menu
                </p>

                @if ($menus->hasPages())
                    <div class="flex flex-wrap items-center gap-1.5">
                        @if ($menus->onFirstPage())
                            <span class="px-3 py-1.5 rounded-lg border border-sand/70 text-xs text-taupe/45 cursor-not-allowed">Sebelumnya</span>
                        @else
                            <a href="{{ $menus->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-sand bg-white text-xs font-semibold text-teak hover:border-copper transition">Sebelumnya</a>
                        @endif

                        @foreach ($menus->getUrlRange(1, $menus->lastPage()) as $page => $url)
                            @if ($page == $menus->currentPage())
                                <span class="w-8 h-8 rounded-lg bg-copper text-white text-xs font-bold flex items-center justify-center tabular-nums">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="w-8 h-8 rounded-lg border border-sand bg-white text-xs font-semibold text-teak hover:border-copper flex items-center justify-center tabular-nums transition">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($menus->hasMorePages())
                            <a href="{{ $menus->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-sand bg-white text-xs font-semibold text-teak hover:border-copper transition">Berikutnya</a>
                        @else
                            <span class="px-3 py-1.5 rounded-lg border border-sand/70 text-xs text-taupe/45 cursor-not-allowed">Berikutnya</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    <p class="mt-4 flex items-center gap-2 text-xs text-taupe">
        <svg class="w-4 h-4 text-taupe/70 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
        </svg>
        <span>Menu berstatus Habis tidak dapat ditambahkan ke pesanan baru. Klik lencana status pada tabel untuk mengubah cepat.</span>
    </p>
@endsection
