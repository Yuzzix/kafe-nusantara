@extends('layouts.app')

@section('title', 'Pencarian')
@section('breadcrumb', 'Beranda / Pencarian')
@section('page_title', 'Hasil Pencarian')

@section('content')
    <div class="mb-5">
        <p class="text-xs text-taupe">
            @if ($q !== '')
                Menampilkan hasil pencarian untuk kata kunci <strong class="text-teak">"{{ $q }}"</strong>.
            @else
                Ketik nama menu, kategori, nomor order, atau nama pelanggan pada kolom pencarian di atas.
            @endif
        </p>
    </div>

    @if ($q !== '')
        <div class="space-y-7">
            {{-- Orders --}}
            <div>
                <h2 class="text-sm font-bold text-teak uppercase tracking-wider mb-2.5">Pesanan ({{ $orders->count() }})</h2>
                @if ($orders->isEmpty())
                    <p class="text-xs text-taupe bg-white border border-sand rounded-lg p-4">Tidak ada pesanan yang cocok.</p>
                @else
                    <div class="bg-white rounded-lg border border-sand divide-y divide-sand/70">
                        @foreach ($orders as $order)
                            <a href="{{ route('orders.show', $order->order_number) }}" class="flex items-center justify-between px-4 py-3 hover:bg-alabaster transition">
                                <div>
                                    <p class="text-sm font-bold text-teak">{{ $order->order_number }} · {{ $order->customer_name ?: 'Tanpa nama' }}</p>
                                    <p class="text-xs text-taupe">{{ $order->type_label }} {{ $order->diningTable ? '· Meja '.$order->diningTable->number : '' }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-status-badge :tone="$order->isPaid() ? 'sage' : 'saffron'" :label="$order->payment_status_label" />
                                    <span class="text-sm font-bold text-teak tabular-nums">{{ $order->formatted_total }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Menus --}}
            <div>
                <h2 class="text-sm font-bold text-teak uppercase tracking-wider mb-2.5">Menu ({{ $menus->count() }})</h2>
                @if ($menus->isEmpty())
                    <p class="text-xs text-taupe bg-white border border-sand rounded-lg p-4">Tidak ada menu yang cocok.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach ($menus as $menu)
                            <x-menu-card :menu="$menu" />
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Categories --}}
            <div>
                <h2 class="text-sm font-bold text-teak uppercase tracking-wider mb-2.5">Kategori ({{ $categories->count() }})</h2>
                @if ($categories->isEmpty())
                    <p class="text-xs text-taupe bg-white border border-sand rounded-lg p-4">Tidak ada kategori yang cocok.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($categories as $category)
                            <x-category-card :category="$category" />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
