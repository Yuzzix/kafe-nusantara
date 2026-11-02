@extends('layouts.app')

@section('title', 'Status Meja')
@section('breadcrumb', 'Beranda / Operasional / Status Meja')
@section('page_title', 'Status Meja')

@section('header_actions')
    <a href="{{ route('tables.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 3v5h-5"/>
        </svg>
        <span>Segarkan Denah</span>
    </a>
@endsection

@section('content')
    {{-- Top 3 Ledger KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <x-stat-card
            label="Meja Tersedia"
            :value="$counts['available'].' meja'"
            hint="Siap digunakan pelanggan baru"
            tone="sage" />

        <x-stat-card
            label="Meja Terisi"
            :value="$counts['occupied'].' meja'"
            hint="Terhubung dengan pesanan aktif"
            tone="clay" />

        <x-stat-card
            label="Reservasi"
            :value="$counts['reserved'].' meja'"
            hint="Dialokasikan sementara"
            tone="saffron" />
    </div>

    {{-- 3 Area Columns: Indoor, Outdoor, VIP --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
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

            <div class="{{ $colSpan }} bg-white rounded-lg border border-sand p-4 shadow-2xs">
                <div class="pb-3 mb-3.5 border-b border-sand flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-teak">{{ $area['title'] }}</h2>
                        <p class="text-[11px] text-taupe">{{ $area['subtitle'] }}</p>
                    </div>
                    <span class="text-xs font-bold text-taupe tabular-nums">{{ $area['tables']->count() }} unit</span>
                </div>

                <div class="kn-stagger grid {{ $innerGrid }} gap-3">
                    @foreach ($area['tables'] as $table)
                        @php
                            $activeOrder = $table->activeOrders->first();
                            $topAccent = match ($table->status) {
                                'occupied' => 'border-t-clay bg-clay-tint/20',
                                'reserved' => 'border-t-saffron bg-saffron-tint/20',
                                default => 'border-t-sage bg-alabaster/45',
                            };
                            $badgeTone = match ($table->status) {
                                'occupied' => 'clay',
                                'reserved' => 'saffron',
                                default => 'sage',
                            };
                        @endphp

                        <div class="kn-card-lift rounded-lg border border-sand border-t-[3px] {{ $topAccent }} p-3.5 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs mb-2.5">
                                    <span class="text-sm font-bold text-teak tabular-nums">{{ $table->number }}</span>
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                onclick="document.getElementById('qr-modal-{{ $table->id }}')?.classList.remove('hidden')"
                                                title="QR Buku Menu Meja {{ $table->number }}"
                                                class="p-1 rounded border border-sand bg-white text-taupe hover:text-copper hover:border-copper transition cursor-pointer">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/>
                                                <path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/>
                                            </svg>
                                        </button>
                                        <span class="text-[11px] font-semibold text-taupe tabular-nums">{{ $table->capacity }} kursi</span>
                                    </div>
                                </div>

                                <x-status-badge
                                    :tone="$badgeTone"
                                    :label="\App\Models\DiningTable::STATUSES[$table->status] ?? $table->status" />
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-sand/70 text-[11px]">
                                @if ($table->status === 'occupied' && $activeOrder)
                                    <a href="{{ route('orders.show', $activeOrder->order_number) }}"
                                       class="block font-bold text-copper hover:underline truncate tabular-nums">
                                        {{ $activeOrder->order_number }} →
                                    </a>
                                @elseif ($table->status === 'available')
                                    <div class="flex items-center justify-between gap-1">
                                        <a href="{{ route('orders.create', ['table' => $table->number]) }}"
                                           class="font-bold text-copper hover:underline">
                                            + Order
                                        </a>
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
                                        <button type="submit" class="font-bold text-sage hover:underline cursor-pointer">
                                            Set Tersedia
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

    {{-- Table Standing QR Tent Card Modals --}}
    @foreach ($tables as $table)
        <div id="qr-modal-{{ $table->id }}"
             onclick="if (event.target === this) this.classList.add('hidden')"
             class="no-print hidden fixed inset-0 z-50 bg-teak/55 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-xs rounded-lg kn-batik-banner text-alabaster border border-brass/40 shadow-2xl p-5 text-center kn-enter">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-brass/25">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-brass">Kartu Stand Meja</span>
                    <button type="button"
                            onclick="document.getElementById('qr-modal-{{ $table->id }}').classList.add('hidden')"
                            class="w-6 h-6 rounded border border-white/20 text-alabaster/75 hover:text-white text-xs cursor-pointer">
                        ✕
                    </button>
                </div>

                <p class="font-serif text-lg font-bold text-white">Kafe Nusantara</p>
                <p class="text-xs text-brass font-semibold mt-0.5 mb-3">Meja {{ $table->number }} · Area {{ $table->area }} ({{ $table->capacity }} Kursi)</p>

                <div class="bg-white rounded-lg p-3 inline-block shadow-md">
                    <x-qr-matrix :payload="route('menu-book', ['meja' => $table->number])" :size="164" />
                </div>

                <p class="text-[11px] text-alabaster/75 mt-3">
                     Arahkan kamera ponsel ke kode QR untuk membuka daftar menu meja ini.
                </p>

                <a href="{{ route('menu-book', ['meja' => $table->number]) }}"
                   class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-bold transition">
                    <span>Buka Buku Menu Meja {{ $table->number }}</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    @endforeach
@endsection
