@extends('layouts.app')

@php
    $showManagerAnalytics = ! isset($activeStaff) || ! $activeStaff || $activeStaff->canViewManagerAnalytics();
@endphp

@section('title', $showManagerAnalytics ? 'Ringkasan Gerai' : 'Ringkasan Shift Kasir')
@section('breadcrumb', 'Beranda / Ringkasan Operasional')
@section('page_title', $showManagerAnalytics ? 'Ringkasan Gerai' : 'Ringkasan Shift Kasir')

@section('header_actions')
    <a href="{{ route('orders.create') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Buat Pesanan Baru</span>
    </a>
@endsection

@section('content')
    {{-- Compact Operational Status Strip --}}
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4 text-xs text-taupe">
        <span>Laporan per <strong class="text-teak">{{ now()->translatedFormat('l, d F Y · H:i') }} WIB</strong></span>
        <div class="flex items-center gap-2">
            <x-status-badge tone="sage" :label="$catalogStats['menus'].' menu aktif · '.$catalogStats['categories'].' kategori'" />
            @if ($catalogStats['unavailable_menus'] > 0)
                <x-status-badge tone="clay" :label="$catalogStats['unavailable_menus'].' menu habis'" />
            @endif
        </div>
    </div>

    {{-- Top Ledger KPI Cards (4 for Admin, 3 daily shift cards for Kasir) --}}
    <div class="kn-stagger grid grid-cols-1 sm:grid-cols-2 {{ $showManagerAnalytics ? 'xl:grid-cols-4' : 'xl:grid-cols-3' }} gap-4 mb-6">
        <x-stat-card
            label="Pendapatan Hari Ini"
            :value="'Rp '.number_format($todayRevenue, 0, ',', '.')"
            :hint="$todayCompletedCount.' pesanan lunas hari ini'"
            tone="copper">
            <x-slot:icon>
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>

        @if ($showManagerAnalytics)
            <x-stat-card
                label="Pendapatan 30 Hari"
                :value="'Rp '.number_format($monthRevenue, 0, ',', '.')"
                :hint="$monthOrdersCount.' transaksi selesai'"
                tone="brass">
                <x-slot:icon>
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
                    </svg>
                </x-slot:icon>
            </x-stat-card>
        @endif

        <x-stat-card
            label="Pesanan Sedang Berjalan"
            :value="$activeOrders->count().' pesanan'"
            hint="Menunggu, diproses, atau disajikan"
            tone="saffron">
            <x-slot:icon>
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card
            label="Ketersediaan Meja"
            :value="$tableCounts['available'].' / '.$tableCounts['total'].' meja'"
            :hint="$tableCounts['occupied'].' terisi · '.$tableCounts['reserved'].' dipesan'"
            tone="sage">
            <x-slot:icon>
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/>
                    <path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v2H6v-2a2 2 0 0 0-4 0Z"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    {{-- Middle Grid: 7-Day Revenue Bar Chart (Admin only) + Top 5 Best-Selling Menus --}}
    @if ($showManagerAnalytics)
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
        {{-- 7-Day Revenue Trend --}}
        <div class="xl:col-span-2 bg-white rounded-lg border border-sand p-5 flex flex-col justify-between shadow-2xs">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-sm font-bold text-teak">Pendapatan 7 Hari Terakhir</h2>
                    <p class="text-xs text-taupe mt-0.5">Berdasarkan transaksi berstatus Lunas & Selesai.</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-copper hover:underline">
                    Riwayat transaksi →
                </a>
            </div>

            <div class="grid grid-cols-7 gap-3 items-end h-44 pt-4 border-b border-sand/70 pb-3">
                @foreach ($dailyTrend as $point)
                    @php
                        $heightPct = $point['revenue'] > 0
                            ? max(12, (int) round(($point['revenue'] / $maxDailyRevenue) * 100))
                            : 4;
                        $isToday = $point['date']->isToday();
                    @endphp
                    <div class="flex flex-col items-center h-full justify-end group">
                        <span class="text-[11px] font-semibold text-taupe tabular-nums mb-1.5">
                            {{ $point['revenue'] > 0 ? number_format($point['revenue'] / 1000, 0, ',', '.').'k' : '0' }}
                        </span>
                        <div class="kn-bar-grow w-full max-w-[38px] rounded-t transition-all {{ $isToday ? 'bg-copper' : 'bg-teak/20 group-hover:bg-copper/75' }}"
                             style="height: {{ $heightPct }}%; animation-delay: {{ $loop->index * 45 }}ms;"
                             title="{{ $point['label'] }}: Rp {{ number_format($point['revenue'], 0, ',', '.') }} ({{ $point['orders_count'] }} pesanan)">
                        </div>
                        <span class="text-xs font-semibold {{ $isToday ? 'text-copper' : 'text-teak' }} mt-2">{{ $point['label'] }}</span>
                        <span class="text-[10.5px] text-taupe tabular-nums">{{ $point['orders_count'] }} trx</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top 5 Best-Selling Menus --}}
        <div class="bg-white rounded-lg border border-sand p-5 shadow-2xs">
            <div class="flex items-start justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-sm font-bold text-teak">5 Sajian Terlaris</h2>
                    <p class="text-xs text-taupe mt-0.5">Total akumulasi porsi terjual.</p>
                </div>
                <a href="{{ route('menus.index') }}" class="text-xs font-semibold text-copper hover:underline">
                    Daftar menu →
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($topMenus as $index => $menu)
                    <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-sand/60 last:border-0 last:pb-0">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-5 h-5 rounded {{ $index === 0 ? 'bg-copper text-white' : 'bg-sand-soft text-teak' }} text-[11px] font-bold flex items-center justify-center shrink-0 tabular-nums">
                                {{ $index + 1 }}
                            </span>
                            @if ($menu->image_url)
                                <div class="w-9 h-9 rounded-md overflow-hidden border border-sand bg-sand-soft shrink-0">
                                    <img src="{{ $menu->image_url }}"
                                         alt="{{ $menu->name }}"
                                         loading="lazy"
                                         decoding="async"
                                         class="w-full h-full max-w-none object-cover">
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-teak truncate">{{ $menu->name }}</p>
                                <p class="text-[11px] text-taupe truncate">{{ $menu->category?->name }} · {{ $menu->formatted_price }}</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-teak bg-sand-soft px-2 py-0.5 rounded shrink-0 tabular-nums">
                            {{ (int) $menu->sold_qty }} porsi
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-taupe">Belum ada histori penjualan menu.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    {{-- Live Active Orders Queue --}}
    <div class="bg-white rounded-lg border border-sand overflow-hidden shadow-2xs">
        <div class="px-5 py-4 border-b border-sand flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-teak">Antrean Pesanan Berjalan</h2>
                <p class="text-xs text-taupe mt-0.5">Pesanan yang sedang diproses dapur atau menunggu penyelesaian kasir.</p>
            </div>
            <div class="flex items-center gap-4">
                @if (! isset($activeStaff) || ! $activeStaff || $activeStaff->canViewKitchen())
                    <a href="{{ route('kitchen.index') }}" class="text-xs font-semibold text-taupe hover:text-copper">
                        Layar Dapur
                    </a>
                @endif
                <a href="{{ route('tables.index') }}" class="text-xs font-semibold text-taupe hover:text-teak">
                    Status Meja
                </a>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-copper hover:underline">
                    Semua Pesanan →
                </a>
            </div>
        </div>

        @if ($activeOrders->isEmpty())
            <div class="p-8 text-center">
                <p class="text-sm font-semibold text-teak">Tidak ada pesanan aktif saat ini</p>
                <p class="text-xs text-taupe mt-1">Seluruh transaksi telah selesai dilayani.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-sand-soft/65 text-left text-[11px] uppercase tracking-wider font-bold text-taupe border-b border-sand">
                            <th class="px-5 py-3">No. Order</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Layanan & Meja</th>
                            <th class="px-4 py-3">Jumlah Item</th>
                            <th class="px-4 py-3">Status Dapur</th>
                            <th class="px-5 py-3 text-right">Tagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand/70">
                        @foreach ($activeOrders as $order)
                            <tr class="hover:bg-alabaster/60 transition">
                                <td class="px-6 py-4 font-bold text-teak tabular-nums">
                                    <a href="{{ route('orders.show', $order->order_number) }}" class="hover:text-copper">
                                        {{ $order->order_number }}
                                    </a>
                                    <span class="block text-[11px] font-normal text-taupe">{{ $order->created_at->format('H:i') }} WIB</span>
                                </td>
                                <td class="px-4 py-4 font-medium text-teak">
                                    {{ $order->customer_name ?: 'Tanpa nama' }}
                                </td>
                                <td class="px-4 py-4 text-taupe">
                                    {{ $order->type_label }}
                                    @if ($order->diningTable)
                                        <span class="ml-1.5 font-bold text-teak">· {{ $order->diningTable->number }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-taupe tabular-nums">{{ $order->items_count }} item</td>
                                <td class="px-4 py-4">
                                    <x-status-badge
                                        :tone="$order->status === 'served' ? 'sage' : 'saffron'"
                                        :label="$order->status_label" />
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-teak tabular-nums">
                                    <a href="{{ route('orders.show', $order->order_number) }}" class="inline-flex items-center gap-2 hover:text-copper">
                                        <span>{{ $order->formatted_total }}</span>
                                        <span class="text-taupe/60">›</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
