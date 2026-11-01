@extends('layouts.app')

@section('title', 'Pesanan & Kasir')
@section('breadcrumb', 'Ruang kerja / Transaksi / Pesanan & Kasir')
@section('page_title', 'Pesanan & Kasir')

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
    {{-- Page Intro & Unpaid/Paid Summary Badges (Matches PDF Page 5) --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-5">
        <div>
            <h2 class="text-xl font-bold text-teak">Pelayanan yang selalu terpantau</h2>
            <p class="text-sm text-taupe mt-0.5">
                Menampilkan pesanan berjalan di bagian teratas agar kasir dan dapur mudah menindaklanjuti.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <x-status-badge tone="saffron" :label="$unpaidCount.' belum bayar'" />
            <x-status-badge tone="sage" :label="$paidCount.' lunas'" />
        </div>
    </div>

    {{-- Filter Bar (Matches PDF Page 5) --}}
    <form action="{{ route('orders.index') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap items-center gap-2.5">
            <select name="date"
                    onchange="this.form.submit()"
                    class="rounded-xl bg-white border {{ request()->filled('date') && request('date') !== 'all' ? 'border-copper text-copper font-semibold' : 'border-sand text-teak font-medium' }} px-3.5 py-2.5 text-xs focus:outline-none focus:border-copper">
                <option value="all" {{ request('date', 'all') === 'all' ? 'selected' : '' }}>Semua tanggal</option>
                <option value="today" {{ request('date') === 'today' ? 'selected' : '' }}>Hari ini ({{ today()->translatedFormat('d M Y') }})</option>
                @foreach ($availableDates as $dateStr)
                    <option value="{{ $dateStr }}" {{ request('date') === $dateStr ? 'selected' : '' }}>
                        {{ \Illuminate\Support\Carbon::parse($dateStr)->translatedFormat('d F Y') }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                    onchange="this.form.submit()"
                    class="rounded-xl bg-white border border-sand px-3.5 py-2.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Semua status dapur</option>
                @foreach (\App\Models\Order::STATUSES as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="payment_status"
                    onchange="this.form.submit()"
                    class="rounded-xl bg-white border border-sand px-3.5 py-2.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Semua pembayaran</option>
                @foreach (\App\Models\Order::PAYMENT_STATUSES as $val => $label)
                    <option value="{{ $val }}" {{ request('payment_status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            @if (request()->hasAny(['date', 'status', 'payment_status', 'search']))
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-copper hover:underline px-2 py-1">
                    Reset
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
                   placeholder="Cari order, pelanggan, atau meja..."
                   class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white border border-sand text-xs text-teak placeholder:text-taupe/65 focus:outline-none focus:border-copper">
        </div>
    </form>

    {{-- Orders Master Table Card (Matches PDF Page 5) --}}
    <div class="bg-white rounded-2xl border border-sand overflow-hidden">
        @if ($orders->isEmpty())
            <div class="p-12 text-center">
                <p class="text-base font-bold text-teak">Belum ada pesanan yang sesuai filter</p>
                <p class="text-xs text-taupe mt-1 mb-5">Ubah filter pencarian atau mulai catat pesanan baru.</p>
                <a href="{{ route('orders.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-copper text-white text-xs font-semibold">
                    + Buat Pesanan Baru
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-alabaster/70 text-left text-xs font-semibold text-taupe border-b border-sand">
                            <th class="pl-6 pr-4 py-3.5">No. Order</th>
                            <th class="px-4 py-3.5">Pelanggan</th>
                            <th class="px-4 py-3.5">Tipe</th>
                            <th class="px-4 py-3.5">Meja</th>
                            <th class="px-4 py-3.5">Status Dapur</th>
                            <th class="px-4 py-3.5">Status Bayar</th>
                            <th class="pl-4 pr-6 py-3.5 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand/70">
                        @foreach ($orders as $order)
                            @php
                                $kitchenTone = match ($order->status) {
                                    'completed', 'served' => 'sage',
                                    'cancelled' => 'clay',
                                    default => 'saffron',
                                };
                                $paymentTone = $order->isPaid()
                                    ? 'sage'
                                    : ($order->status === 'cancelled' ? 'neutral' : 'saffron');
                                $isActiveRow = in_array($order->status, \App\Models\Order::ACTIVE_STATUSES, true);
                            @endphp
                            <tr onclick="window.location='{{ route('orders.show', $order->order_number) }}'"
                                class="cursor-pointer transition {{ $isActiveRow ? 'bg-copper-tint/35 hover:bg-copper-tint/70' : 'hover:bg-alabaster/70' }}">
                                <td class="pl-6 pr-4 py-4 font-bold text-teak tabular-nums">
                                    <div class="flex items-center gap-2">
                                        @if ($isActiveRow)
                                            <span class="w-1 h-5 rounded-full bg-copper shrink-0"></span>
                                        @endif
                                        <div>
                                            <span>{{ $order->order_number }}</span>
                                            <span class="block text-[11px] font-normal text-taupe">
                                                {{ $order->created_at->translatedFormat('d M Y · H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 font-medium text-teak">
                                    {{ $order->customer_name ?: 'Tanpa nama' }}
                                </td>
                                <td class="px-4 py-4 text-taupe">
                                    {{ $order->type_label }}
                                </td>
                                <td class="px-4 py-4 font-bold text-teak">
                                    {{ $order->diningTable?->number ?: '—' }}
                                </td>
                                <td class="px-4 py-4">
                                    <x-status-badge :tone="$kitchenTone" :label="$order->status_label" />
                                </td>
                                <td class="px-4 py-4">
                                    <x-status-badge :tone="$paymentTone" :label="$order->payment_status_label" />
                                </td>
                                <td class="pl-4 pr-6 py-4 text-right font-bold text-teak tabular-nums">
                                    <span class="inline-flex items-center gap-2">
                                        <span>{{ $order->formatted_total }}</span>
                                        <span class="text-taupe/60">›</span>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer Bar: Pagination + "Unduh Daftar" CSV Export Button (Matches PDF Page 5) --}}
            <div class="px-6 py-4 border-t border-sand flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <p class="text-xs text-taupe tabular-nums">
                    Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($orders->hasPages())
                        <div class="flex items-center gap-1.5">
                            @if (! $orders->onFirstPage())
                                <a href="{{ $orders->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-sand bg-white text-xs font-semibold text-teak hover:border-copper">← Sebelumnya</a>
                            @endif
                            <span class="px-2.5 py-1 text-xs text-taupe tabular-nums">
                                Hal. {{ $orders->currentPage() }} / {{ $orders->lastPage() }}
                            </span>
                            @if ($orders->hasMorePages())
                                <a href="{{ $orders->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg border border-sand bg-white text-xs font-semibold text-teak hover:border-copper">Berikutnya →</a>
                            @endif
                        </div>
                    @endif

                    <a href="{{ route('orders.export', request()->query()) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-sand bg-white text-xs font-semibold text-teak hover:border-copper transition">
                        <svg class="w-3.5 h-3.5 text-taupe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                        </svg>
                        <span>Unduh Daftar</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <p class="mt-4 flex items-center gap-2 text-xs text-taupe">
        <svg class="w-4 h-4 text-taupe/70 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
        </svg>
        <span>Pilih baris pesanan untuk melihat rincian sajian, memperbarui status dapur, dan menerima pembayaran kasir.</span>
    </p>
@endsection
