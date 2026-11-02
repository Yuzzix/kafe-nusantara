@extends('layouts.app')

@section('title', 'Layar Dapur (KDS)')
@section('breadcrumb', 'Beranda / Operasional / Layar Dapur')
@section('page_title', 'Layar Dapur (Kitchen Display System)')

@section('header_actions')
    <a href="{{ route('kitchen.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v5h-5"/>
        </svg>
        <span>Muat Ulang Antrean</span>
    </a>
@endsection

@php
    $canOpenOrderDetail = ! isset($activeStaff) || ! $activeStaff || $activeStaff->canAccessOrders();
    $canProcessPayment = ! isset($activeStaff) || ! $activeStaff || $activeStaff->canProcessPayment();
@endphp

@section('content')
    {{-- Top 3 Queue Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <x-stat-card
            label="Menunggu Antrean"
            :value="$pendingOrders->count().' tiket'"
            hint="Pesanan baru dari kasir"
            tone="saffron" />
        <x-stat-card
            label="Sedang Dimasak"
            :value="$preparingOrders->count().' tiket'"
            hint="Dalam pengerjaan dapur/barista"
            tone="copper" />
        <x-stat-card
            label="Telah Disajikan"
            :value="$servedOrders->count().' meja'"
            hint="Menunggu penyelesaian kasir"
            tone="sage" />
    </div>

    {{-- 3-Column Kitchen Ticket Board --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
        {{-- Column 1: Menunggu Antrean (pending) --}}
        <div class="bg-sand-soft/55 rounded-lg border border-sand p-3.5 space-y-3">
            <div class="flex items-center justify-between px-1 pb-2 border-b border-sand">
                <h2 class="text-xs font-bold text-teak uppercase tracking-wider">1 · Menunggu Antrean</h2>
                <span class="px-2 py-0.5 rounded bg-saffron-tint text-saffron text-[11px] font-bold tabular-nums">
                    {{ $pendingOrders->count() }}
                </span>
            </div>

            @forelse ($pendingOrders as $order)
                <div class="bg-white rounded-lg border border-sand border-t-[3px] border-t-saffron p-4 space-y-3 shadow-2xs">
                    <div class="flex items-start justify-between gap-2 pb-2.5 border-b border-dashed border-sand">
                        <div>
                            @if ($canOpenOrderDetail)
                                <a href="{{ route('orders.show', $order->order_number) }}" class="text-sm font-bold text-teak hover:text-copper tabular-nums">
                                    {{ $order->order_number }}
                                </a>
                            @else
                                <span class="text-sm font-bold text-teak tabular-nums">{{ $order->order_number }}</span>
                            @endif
                            <p class="text-[11px] text-taupe mt-0.5">
                                {{ $order->customer_name ?: 'Tanpa nama' }} · {{ $order->created_at->format('H:i') }} WIB
                            </p>
                        </div>

                        @if ($order->diningTable)
                            <span class="px-2 py-0.5 rounded bg-teak text-white text-xs font-bold shrink-0">
                                Meja {{ $order->diningTable->number }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-brass-tint text-saffron border border-brass/30 text-xs font-bold shrink-0">
                                Bawa Pulang
                            </span>
                        @endif
                    </div>

                    <ul class="space-y-2 text-xs">
                        @foreach ($order->items as $item)
                            <li>
                                <span class="font-bold text-copper tabular-nums">{{ $item->quantity }}×</span>
                                <span class="font-semibold text-teak ml-1">{{ $item->menu?->name ?? 'Menu' }}</span>
                                @if ($item->notes)
                                    <p class="mt-0.5 text-[11px] font-semibold text-copper bg-copper-tint px-2 py-0.5 rounded inline-block">
                                        Catatan: {{ $item->notes }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($order->notes)
                        <div class="rounded bg-alabaster border border-sand px-2.5 py-1.5 text-[11px] text-taupe">
                            <strong class="text-teak">Catatan:</strong> {{ $order->notes }}
                        </div>
                    @endif

                    <form action="{{ route('orders.update-status', $order->order_number) }}" method="POST" class="pt-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="preparing">
                        <button type="submit"
                                class="w-full py-2 px-3 rounded-md bg-copper hover:bg-copper-dark text-white text-xs font-bold transition cursor-pointer">
                            Mulai Proses Masak →
                        </button>
                    </form>
                </div>
            @empty
                <div class="bg-white/75 rounded-lg border border-dashed border-sand p-6 text-center">
                    <p class="text-xs font-semibold text-taupe">Tidak ada antrean masuk</p>
                </div>
            @endforelse
        </div>

        {{-- Column 2: Sedang Dimasak (preparing) --}}
        <div class="bg-sand-soft/55 rounded-lg border border-sand p-3.5 space-y-3">
            <div class="flex items-center justify-between px-1 pb-2 border-b border-sand">
                <h2 class="text-xs font-bold text-teak uppercase tracking-wider">2 · Sedang Dimasak</h2>
                <span class="px-2 py-0.5 rounded bg-copper-tint text-copper text-[11px] font-bold tabular-nums">
                    {{ $preparingOrders->count() }}
                </span>
            </div>

            @forelse ($preparingOrders as $order)
                <div class="bg-white rounded-lg border border-sand border-t-[3px] border-t-copper p-4 space-y-3 shadow-2xs">
                    <div class="flex items-start justify-between gap-2 pb-2.5 border-b border-dashed border-sand">
                        <div>
                            @if ($canOpenOrderDetail)
                                <a href="{{ route('orders.show', $order->order_number) }}" class="text-sm font-bold text-teak hover:text-copper tabular-nums">
                                    {{ $order->order_number }}
                                </a>
                            @else
                                <span class="text-sm font-bold text-teak tabular-nums">{{ $order->order_number }}</span>
                            @endif
                            <p class="text-[11px] text-taupe mt-0.5">
                                {{ $order->customer_name ?: 'Tanpa nama' }} · {{ $order->created_at->format('H:i') }} WIB
                            </p>
                        </div>

                        @if ($order->diningTable)
                            <span class="px-2 py-0.5 rounded bg-teak text-white text-xs font-bold shrink-0">
                                Meja {{ $order->diningTable->number }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-brass-tint text-saffron border border-brass/30 text-xs font-bold shrink-0">
                                Bawa Pulang
                            </span>
                        @endif
                    </div>

                    <ul class="space-y-2 text-xs">
                        @foreach ($order->items as $item)
                            <li>
                                <span class="font-bold text-copper tabular-nums">{{ $item->quantity }}×</span>
                                <span class="font-semibold text-teak ml-1">{{ $item->menu?->name ?? 'Menu' }}</span>
                                @if ($item->notes)
                                    <p class="mt-0.5 text-[11px] font-semibold text-copper bg-copper-tint px-2 py-0.5 rounded inline-block">
                                        Catatan: {{ $item->notes }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($order->notes)
                        <div class="rounded bg-alabaster border border-sand px-2.5 py-1.5 text-[11px] text-taupe">
                            <strong class="text-teak">Catatan:</strong> {{ $order->notes }}
                        </div>
                    @endif

                    <form action="{{ route('orders.update-status', $order->order_number) }}" method="POST" class="pt-1">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="served">
                        <button type="submit"
                                class="w-full py-2 px-3 rounded-md bg-sage hover:opacity-95 text-white text-xs font-bold transition cursor-pointer">
                            ✓ Tandai Selesai & Disajikan
                        </button>
                    </form>
                </div>
            @empty
                <div class="bg-white/75 rounded-lg border border-dashed border-sand p-6 text-center">
                    <p class="text-xs font-semibold text-taupe">Tidak ada pesanan sedang dimasak</p>
                </div>
            @endforelse
        </div>

        {{-- Column 3: Telah Disajikan (served) --}}
        <div class="bg-sand-soft/55 rounded-lg border border-sand p-3.5 space-y-3">
            <div class="flex items-center justify-between px-1 pb-2 border-b border-sand">
                <h2 class="text-xs font-bold text-teak uppercase tracking-wider">3 · Telah Disajikan</h2>
                <span class="px-2 py-0.5 rounded bg-sage-tint text-sage text-[11px] font-bold tabular-nums">
                    {{ $servedOrders->count() }}
                </span>
            </div>

            @forelse ($servedOrders as $order)
                <div class="bg-white rounded-lg border border-sand border-t-[3px] border-t-sage p-4 space-y-2.5 shadow-2xs">
                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-sand/60">
                        <div>
                            @if ($canOpenOrderDetail)
                                <a href="{{ route('orders.show', $order->order_number) }}" class="text-sm font-bold text-teak hover:text-copper tabular-nums">
                                    {{ $order->order_number }}
                                </a>
                            @else
                                <span class="text-sm font-bold text-teak tabular-nums">{{ $order->order_number }}</span>
                            @endif
                            <p class="text-[11px] text-taupe mt-0.5">
                                {{ $order->customer_name ?: 'Tanpa nama' }} · {{ $order->items->sum('quantity') }} porsi
                            </p>
                        </div>

                        @if ($order->diningTable)
                            <span class="px-2 py-0.5 rounded bg-sage-tint text-sage text-xs font-bold shrink-0">
                                Meja {{ $order->diningTable->number }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-alabaster border border-sand text-taupe text-xs font-semibold shrink-0">
                                Bawa Pulang
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-teak tabular-nums">{{ $order->formatted_total }}</span>
                        @if ($canProcessPayment)
                            <a href="{{ route('orders.show', $order->order_number) }}"
                               class="font-bold text-copper hover:underline">
                                Proses Kasir →
                            </a>
                        @else
                            <span class="text-sage font-semibold">Menunggu Kasir</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white/75 rounded-lg border border-dashed border-sand p-6 text-center">
                    <p class="text-xs font-semibold text-taupe">Tidak ada tagihan menunggu</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
