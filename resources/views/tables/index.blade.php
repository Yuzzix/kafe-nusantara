@extends('layouts.app')

@section('title', 'Status Meja')
@section('breadcrumb', 'Ruang kerja / Layanan / Status Meja')
@section('page_title', 'Status Meja')

@section('header_actions')
    <a href="{{ route('tables.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v5h-5"/>
        </svg>
        <span>Segarkan Status</span>
    </a>
@endsection

@section('content')
    {{-- Page Intro (Matches PDF Page 8) --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-teak">Ruang yang siap menyambut</h2>
            <p class="text-sm text-taupe mt-0.5">
                Pantau ketersediaan {{ $tables->count() }} meja di seluruh area Kafe Nusantara.
            </p>
        </div>

        <span class="text-xs text-taupe">Data per {{ now()->translatedFormat('d F Y · H:i') }} WIB</span>
    </div>

    {{-- Top 3 Summary KPI Cards (Matches PDF Page 8) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-sand p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sage-tint text-sage font-bold text-xl flex items-center justify-center tabular-nums">
                {{ $counts['available'] }}
            </div>
            <div>
                <p class="text-sm font-bold text-teak">Tersedia</p>
                <p class="text-xs text-taupe mt-0.5">Siap untuk tamu berikutnya</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-sand p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-clay-tint text-clay font-bold text-xl flex items-center justify-center tabular-nums">
                {{ $counts['occupied'] }}
            </div>
            <div>
                <p class="text-sm font-bold text-teak">Terisi</p>
                <p class="text-xs text-taupe mt-0.5">Sedang melayani pelanggan</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-sand p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-saffron-tint text-saffron font-bold text-xl flex items-center justify-center tabular-nums">
                {{ $counts['reserved'] }}
            </div>
            <div>
                <p class="text-sm font-bold text-teak">Dipesan</p>
                <p class="text-xs text-taupe mt-0.5">Dialokasikan untuk reservasi</p>
            </div>
        </div>
    </div>

    {{-- 3 Area Columns: Dalam ruangan (6 cols), Luar ruangan (4 cols), VIP (2 cols) — Matches PDF Page 8 --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
        @foreach ($areas as $areaKey => $area)
            @php
                $colSpan = match ($areaKey) {
                    'Indoor' => 'xl:col-span-6',
                    'Outdoor' => 'xl:col-span-4',
                    default => 'xl:col-span-2',
                };
                $innerGrid = match ($areaKey) {
                    'Indoor' => 'grid-cols-2 sm:grid-cols-3',
                    'Outdoor' => 'grid-cols-2',
                    default => 'grid-cols-2 xl:grid-cols-1',
                };
            @endphp

            <div class="{{ $colSpan }} bg-sand-soft/55 rounded-2xl border border-sand p-5">
                <div class="mb-4">
                    <h3 class="text-base font-bold text-teak">{{ $area['title'] }}</h3>
                    <p class="text-xs text-taupe">{{ $area['subtitle'] }}</p>
                </div>

                <div class="kn-stagger grid {{ $innerGrid }} gap-3.5">
                    @foreach ($area['tables'] as $table)
                        @php
                            $activeOrder = $table->activeOrders->first();
                            $iconBoxClass = match ($table->status) {
                                'occupied' => 'bg-clay-tint text-clay',
                                'reserved' => 'bg-saffron-tint text-saffron',
                                default => 'bg-sage-tint text-sage',
                            };
                            $badgeTone = match ($table->status) {
                                'occupied' => 'clay',
                                'reserved' => 'saffron',
                                default => 'sage',
                            };
                        @endphp

                        <div class="kn-card-lift bg-white rounded-2xl border border-sand p-4 flex flex-col items-center justify-between text-center hover:border-copper/45">
                            {{-- Top Row: Table Number + Capacity --}}
                            <div class="w-full flex items-center justify-between text-xs mb-3">
                                <span class="text-sm font-bold text-teak">{{ $table->number }}</span>
                                <span class="inline-flex items-center gap-1 text-taupe tabular-nums" title="Kapasitas {{ $table->capacity }} kursi">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                    </svg>
                                    {{ $table->capacity }}
                                </span>
                            </div>

                            {{-- Armchair Icon --}}
                            <div class="w-11 h-11 rounded-xl {{ $iconBoxClass }} flex items-center justify-center my-2">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/>
                                    <path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v2H6v-2a2 2 0 0 0-4 0Z"/>
                                    <path d="M4 18v2"/><path d="M20 18v2"/>
                                </svg>
                            </div>

                            {{-- Status Badge --}}
                            <div class="mt-2">
                                <x-status-badge
                                    :tone="$badgeTone"
                                    :label="\App\Models\DiningTable::STATUSES[$table->status] ?? $table->status" />
                            </div>

                            {{-- Contextual Action --}}
                            <div class="mt-3 pt-2.5 border-t border-sand/60 w-full">
                                @if ($table->status === 'occupied' && $activeOrder)
                                    <a href="{{ route('orders.show', $activeOrder->order_number) }}"
                                       class="block text-[11px] font-semibold text-copper hover:underline truncate">
                                        Lihat {{ $activeOrder->order_number }} →
                                    </a>
                                @elseif ($table->status === 'available')
                                    <div class="flex items-center justify-center gap-2 text-[11px]">
                                        <a href="{{ route('orders.create', ['table' => $table->number]) }}"
                                           class="font-semibold text-copper hover:underline">
                                            + Pesanan
                                        </a>
                                        <span class="text-sand">·</span>
                                        <form action="{{ route('tables.update-status', $table) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="reserved">
                                            <button type="submit" class="text-taupe hover:text-teak cursor-pointer">Reservasi</button>
                                        </form>
                                    </div>
                                @elseif ($table->status === 'reserved')
                                    <form action="{{ route('tables.update-status', $table) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="available">
                                        <button type="submit" class="text-[11px] font-semibold text-sage hover:underline cursor-pointer">
                                            Buka Tersedia
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Bottom Info Card (Matches PDF Page 8) --}}
    <div class="mt-5 bg-white rounded-2xl border border-sand px-5 py-4 flex items-start gap-3">
        <svg class="w-4 h-4 text-taupe mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
        </svg>
        <div>
            <p class="text-xs font-bold text-teak">Meja tersedia dapat dipilih saat membuat pesanan baru.</p>
            <p class="text-xs text-taupe mt-0.5">
                Meja berstatus Terisi terhubung otomatis dengan pesanan aktif dan kembali menjadi Tersedia segera setelah pembayaran dikonfirmasi kasir.
            </p>
        </div>
    </div>
@endsection
