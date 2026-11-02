@extends('layouts.app')

@php
    $canProcessPayment = ! isset($activeStaff) || ! $activeStaff || $activeStaff->canProcessPayment();
@endphp

@section('title', $canProcessPayment ? 'Pesanan & Kasir' : 'Pesanan Pelanggan')
@section('breadcrumb', $canProcessPayment ? 'Beranda / Operasional / Pesanan & Kasir' : 'Beranda / Operasional / Pesanan Pelanggan')
@section('page_title', $canProcessPayment ? 'Pesanan & Kasir' : 'Pesanan Pelanggan')

@section('header_actions')
    @if ($canProcessPayment)
        <a href="{{ route('orders.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-sand bg-white text-xs font-semibold text-teak hover:border-copper transition">
            <svg class="w-3.5 h-3.5 text-taupe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
            </svg>
            <span>Ekspor CSV</span>
        </a>
    @endif
    <a href="{{ route('orders.create') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Buat Pesanan Baru</span>
    </a>
@endsection

@section('content')
    {{-- Filter & Counter Toolbar --}}
    <form action="{{ route('orders.index') }}" method="GET" class="bg-white rounded-lg border border-sand p-3.5 mb-4 flex flex-col xl:flex-row xl:items-center justify-between gap-3 shadow-2xs">
        <div class="flex flex-wrap items-center gap-2">
            <select name="date"
                    onchange="this.form.submit()"
                    class="rounded-md bg-alabaster border {{ request()->filled('date') && request('date') !== 'all' ? 'border-copper text-copper font-semibold' : 'border-sand text-teak font-medium' }} px-3 py-1.5 text-xs focus:outline-none focus:border-copper">
                <option value="all" {{ request('date', 'all') === 'all' ? 'selected' : '' }}>Semua Tanggal</option>
                <option value="today" {{ request('date') === 'today' ? 'selected' : '' }}>Hari Ini ({{ today()->translatedFormat('d M Y') }})</option>
                @foreach ($availableDates as $dateStr)
                    <option value="{{ $dateStr }}" {{ request('date') === $dateStr ? 'selected' : '' }}>
                        {{ \Illuminate\Support\Carbon::parse($dateStr)->translatedFormat('d F Y') }}
                    </option>
                @endforeach
            </select>

            <select name="status"
                    onchange="this.form.submit()"
                    class="rounded-md bg-alabaster border border-sand px-3 py-1.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Status Dapur</option>
                @foreach (\App\Models\Order::STATUSES as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="payment_status"
                    onchange="this.form.submit()"
                    class="rounded-md bg-alabaster border border-sand px-3 py-1.5 text-xs font-medium text-teak focus:outline-none focus:border-copper">
                <option value="">Status Bayar</option>
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

        <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-1.5">
                <x-status-badge tone="saffron" :label="$unpaidCount.' belum bayar'" />
                <x-status-badge tone="sage" :label="$paidCount.' lunas'" />
            </div>

            <div class="relative w-full sm:w-60">
                <svg class="w-3.5 h-3.5 text-taupe/70 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="No. order, pelanggan, meja..."
                       class="w-full pl-8 pr-3 py-1.5 rounded-md bg-alabaster border border-sand text-xs text-teak placeholder:text-taupe/65 focus:outline-none focus:bg-white focus:border-copper">
            </div>
        </div>
    </form>

    {{-- Orders Master Table --}}
    <div class="bg-white rounded-lg border border-sand overflow-hidden shadow-2xs">
        @if ($orders->isEmpty())
            <div class="p-10 text-center">
                <p class="text-sm font-bold text-teak">Tidak ada transaksi yang sesuai filter</p>
                <p class="text-xs text-taupe mt-1 mb-4">Sesuaikan kriteria filter atau buat pesanan baru.</p>
                <a href="{{ route('orders.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper text-white text-xs font-semibold">
                    + Buat Pesanan Baru
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-sand-soft/65 text-left text-[11px] uppercase tracking-wider font-bold text-taupe border-b border-sand">
                            <th class="pl-5 pr-4 py-3">No. Order</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Layanan</th>
                            <th class="px-4 py-3">Meja</th>
                            <th class="px-4 py-3">Status Dapur</th>
                            <th class="px-4 py-3">Pembayaran</th>
                            <th class="pl-4 pr-5 py-3 text-right">Total Tagihan</th>
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
                                class="cursor-pointer transition {{ $isActiveRow ? 'bg-copper-tint/35 hover:bg-copper-tint/65' : 'hover:bg-alabaster/65' }}">
                                <td class="pl-5 pr-4 py-3.5 font-bold text-teak tabular-nums">
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
                                <td class="px-4 py-3.5 font-medium text-teak">
                                    {{ $order->customer_name ?: 'Tanpa nama' }}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-taupe">
                                    {{ $order->type_label }}
                                </td>
                                <td class="px-4 py-3.5 font-bold text-teak">
                                    {{ $order->diningTable?->number ?: '—' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <x-status-badge :tone="$kitchenTone" :label="$order->status_label" />
                                </td>
                                <td class="px-4 py-3.5">
                                    <x-status-badge :tone="$paymentTone" :label="$order->payment_status_label" />
                                </td>
                                <td class="pl-4 pr-5 py-3.5 text-right font-bold text-teak tabular-nums">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span>{{ $order->formatted_total }}</span>
                                        <span class="text-taupe/60">›</span>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="px-5 py-3.5 bg-alabaster/40 border-t border-sand flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <p class="text-xs text-taupe tabular-nums">
                    Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} transaksi
                </p>

                @if ($orders->hasPages())
                    <div class="flex items-center gap-1.5">
                        @if (! $orders->onFirstPage())
                            <a href="{{ $orders->previousPageUrl() }}" class="px-2.5 py-1 rounded border border-sand bg-white text-xs font-semibold text-teak hover:border-copper">‹ Sebelumnya</a>
                        @endif
                        <span class="px-2 py-1 text-xs text-taupe tabular-nums">
                            Hal. {{ $orders->currentPage() }} / {{ $orders->lastPage() }}
                        </span>
                        @if ($orders->hasMorePages())
                            <a href="{{ $orders->nextPageUrl() }}" class="px-2.5 py-1 rounded border border-sand bg-white text-xs font-semibold text-teak hover:border-copper">Berikutnya ›</a>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
