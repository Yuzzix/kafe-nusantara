@extends('layouts.app')

@section('title', 'Ringkasan Gerai')
@section('breadcrumb', 'Ruang kerja / Ikhtisar / Ringkasan Gerai')
@section('page_title', 'Ringkasan Gerai')

@section('header_actions')
    <a href="{{ route('orders.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Buat Pesanan Baru</span>
    </a>
@endsection

@section('content')
    {{-- Editorial Subheader --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-teak">Denyut operasional hari ini</h2>
            <p class="text-sm text-taupe mt-0.5">
                Pantau pendapatan, antrean dapur, keterisian meja, dan sajian terlaris Kafe Nusantara secara langsung.
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs text-taupe">
            <x-status-badge tone="sage" :label="$catalogStats['menus'].' menu aktif dalam '.$catalogStats['categories'].' kategori'" />
            @if ($catalogStats['unavailable_menus'] > 0)
                <x-status-badge tone="clay" :label="$catalogStats['unavailable_menus'].' menu habis'" />
            @endif
        </div>
    </div>

    {{-- Top 4 KPI Cards --}}
    <div class="kn-stagger grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-7">
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

    {{-- Middle Grid: 7-Day Revenue Bar Chart + Top 5 Best-Selling Menus --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-7">
        {{-- 7-Day Revenue Trend --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-sand p-6 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-base font-bold text-teak">Tren Pendapatan 7 Hari Terakhir</h3>
                    <p class="text-xs text-taupe mt-0.5">Rekapitulasi pesanan berstatus Lunas & Selesai.</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-copper hover:underline">
                    Semua transaksi →
                </a>
            </div>

            <div class="grid grid-cols-7 gap-3 items-end h-48 pt-6">
                @foreach ($dailyTrend as $point)
                    @php
                        $heightPct = $point['revenue'] > 0
                            ? max(12, (int) round(($point['revenue'] / $maxDailyRevenue) * 100))
                            : 4;
                        $isToday = $point['date']->isToday();
                    @endphp
                    <div class="flex flex-col items-center h-full justify-end group">
                        <span class="text-[11px] font-semibold text-taupe tabular-nums mb-1.5 opacity-80 group-hover:opacity-100">
                            {{ $point['revenue'] > 0 ? number_format($point['revenue'] / 1000, 0, ',', '.').'k' : '0' }}
                        </span>
                        <div class="kn-bar-grow w-full max-w-[44px] rounded-t-xl transition-all {{ $isToday ? 'bg-copper' : 'bg-brass/45 group-hover:bg-copper/75' }}"
                             style="height: {{ $heightPct }}%; animation-delay: {{ $loop->index * 45 }}ms;"
                             title="{{ $point['label'] }}: Rp {{ number_format($point['revenue'], 0, ',', '.') }} ({{ $point['orders_count'] }} pesanan)">
                        </div>
                        <span class="text-xs font-semibold {{ $isToday ? 'text-copper' : 'text-teak' }} mt-2">{{ $point['label'] }}</span>
                        <span class="text-[10.5px] text-taupe tabular-nums">{{ $point['orders_count'] }} order</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top 5 Best-Selling Menus --}}
        <div class="bg-white rounded-2xl border border-sand p-6">
            <div class="flex items-start justify-between gap-2 mb-5">
                <div>
                    <h3 class="text-base font-bold text-teak">5 Sajian Terlaris</h3>
                    <p class="text-xs text-taupe mt-0.5">Berdasarkan total porsi terjual.</p>
                </div>
                <a href="{{ route('menus.index') }}" class="text-xs font-semibold text-copper hover:underline">
                    Katalog →
                </a>
            </div>

            <div class="space-y-3.5">
                @forelse ($topMenus as $index => $menu)
                    <div class="flex items-center justify-between gap-3 pb-3 border-b border-sand/60 last:border-0 last:pb-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-7 h-7 rounded-lg {{ $index === 0 ? 'bg-copper text-white' : 'bg-sand-soft text-teak' }} text-xs font-bold flex items-center justify-center shrink-0 tabular-nums">
                                {{ $index + 1 }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-teak truncate">{{ $menu->name }}</p>
                                <p class="text-xs text-taupe truncate">{{ $menu->category?->name }} · {{ $menu->formatted_price }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-copper bg-copper-tint px-2.5 py-1 rounded-full shrink-0 tabular-nums">
                            {{ (int) $menu->sold_qty }} porsi
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-taupe">Belum ada histori penjualan menu.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Live Active Orders Queue --}}
    <div class="bg-white rounded-2xl border border-sand overflow-hidden">
        <div class="px-6 py-5 border-b border-sand flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-teak">Antrean Pesanan Aktif Saat Ini</h3>
                <p class="text-xs text-taupe mt-0.5">Pesanan yang sedang ditangani dapur atau menunggu pembayaran kasir.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('tables.index') }}" class="text-xs font-semibold text-taupe hover:text-teak">
                    Pantau meja
                </a>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-copper hover:underline">
                    Buka Pesanan & Kasir →
                </a>
            </div>
        </div>

        @if ($activeOrders->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm font-semibold text-teak">Tidak ada pesanan aktif saat ini</p>
                <p class="text-xs text-taupe mt-1">Semua pesanan telah selesai dilayani. Klik "Buat Pesanan Baru" untuk memulai transaksi.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-alabaster/70 text-left text-xs font-semibold text-taupe border-b border-sand">
                            <th class="px-6 py-3.5">No. Order</th>
                            <th class="px-4 py-3.5">Pelanggan</th>
                            <th class="px-4 py-3.5">Tipe & Meja</th>
                            <th class="px-4 py-3.5">Item</th>
                            <th class="px-4 py-3.5">Status Dapur</th>
                            <th class="px-6 py-3.5 text-right">Total</th>
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
