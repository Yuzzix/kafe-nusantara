@extends('layouts.app')

@section('title', 'Detail Pesanan '.$order->order_number)
@section('breadcrumb')
    <a href="{{ route('orders.index') }}" class="hover:text-copper">Beranda / Pesanan & Kasir</a> / {{ $order->order_number }}
@endsection
@section('page_title', 'Rincian Pesanan')

@section('header_actions')
    <button type="button"
            onclick="document.getElementById('receipt-preview-modal')?.classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition cursor-pointer">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>
        </svg>
        <span>Cetak Struk</span>
    </button>
@endsection

@section('content')
    @php
        $kitchenTone = match ($order->status) {
            'completed', 'served' => 'sage',
            'cancelled' => 'clay',
            default => 'saffron',
        };
        $paymentTone = $order->isPaid()
            ? 'sage'
            : ($order->status === 'cancelled' ? 'neutral' : 'saffron');
        $customerDisplay = $order->customer_name ?: 'Tanpa nama';
        $customerInitial = mb_strtoupper(mb_substr($customerDisplay, 0, 1));
    @endphp

    {{-- Top Order Header Row (Matches PDF Page 6) --}}
    <div class="kn-screen-only flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-teak tabular-nums">{{ $order->order_number }}</h2>
            <p class="text-xs text-taupe mt-1">
                {{ $order->created_at->translatedFormat('d F Y · H:i:s') }}
                · Kasir/Staf: <span class="font-medium text-teak">{{ $order->user?->name ?: 'Staf Gerai' }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <x-status-badge :tone="$kitchenTone" :label="$order->status_label" />
            <x-status-badge :tone="$paymentTone" :label="$order->payment_status_label" />
        </div>
    </div>

    {{-- Kitchen Workflow Action Strip (When Unpaid & Not Cancelled — solves the static-badge gap!) --}}
    @if (! $order->isPaid() && $order->status !== 'cancelled')
        <div class="no-print mb-5 bg-white rounded-lg border border-sand px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5 text-xs">
                <span class="font-bold text-teak">Alur Dapur & Sajian:</span>
                <span class="text-taupe">Perbarui progres sebelum atau sesudah hidangan diantar ke meja pelanggan.</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($order->status === 'pending')
                    <form action="{{ route('orders.update-status', $order->order_number) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="preparing">
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-saffron-tint border border-saffron/30 text-xs font-bold text-saffron hover:bg-saffron hover:text-white transition cursor-pointer">
                            Mulai Proses Dapur →
                        </button>
                    </form>
                @endif

                @if (in_array($order->status, ['pending', 'preparing'], true))
                    <form action="{{ route('orders.update-status', $order->order_number) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="served">
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-sage-tint border border-sage/30 text-xs font-bold text-sage hover:bg-sage hover:text-white transition cursor-pointer">
                            ✓ Tandai Sudah Disajikan
                        </button>
                    </form>
                @endif

                <form action="{{ route('orders.update-status', $order->order_number) }}"
                      method="POST"
                      onsubmit="return confirm('Batalkan pesanan {{ $order->order_number }}? Meja terkait akan otomatis dibebaskan.')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="px-3 py-1.5 rounded-xl border border-sand text-xs font-semibold text-clay hover:bg-clay-tint transition cursor-pointer">
                        Batalkan Pesanan
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Main 2-Column Layout: Left Itemized Bill + Right Payment Panel --}}
    <div class="kn-screen-only grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
        {{-- Left 7 Columns: Itemized Order Details --}}
        <div class="xl:col-span-7 bg-white rounded-lg border border-sand p-6 shadow-2xs">
            {{-- Customer & Table Header --}}
            <div class="flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-sand">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-sand-soft text-teak font-bold text-sm flex items-center justify-center">
                        {{ $customerInitial }}
                    </div>
                    <div>
                        <p class="text-base font-bold text-teak">{{ $customerDisplay }}</p>
                        <p class="text-xs text-taupe">{{ $order->type_label }}</p>
                    </div>
                </div>

                @if ($order->diningTable)
                    <div class="inline-flex items-center gap-2 text-sm text-teak">
                        <svg class="w-4 h-4 text-taupe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/><path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v2H6v-2a2 2 0 0 0-4 0Z"/>
                        </svg>
                        <span class="font-bold">{{ $order->diningTable->number }}</span>
                        <span class="text-xs text-taupe">· {{ $order->diningTable->capacity }} kursi</span>
                    </div>
                @else
                    <span class="text-xs font-semibold text-taupe bg-alabaster px-3 py-1.5 rounded-lg border border-sand">
                        Pesanan Bawa Pulang
                    </span>
                @endif
            </div>

            {{-- Order Items Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-taupe border-b border-sand/70">
                            <th class="py-3.5 pr-4">Menu / Catatan</th>
                            <th class="py-3.5 px-3 text-center">Jml</th>
                            <th class="py-3.5 px-3 text-right">Harga Satuan</th>
                            <th class="py-3.5 pl-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand/60">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-4 pr-4">
                                    <p class="font-bold text-teak">{{ $item->menu?->name ?? 'Menu Terarsip' }}</p>
                                    <p class="text-xs {{ $item->notes ? 'text-copper font-medium' : 'text-taupe/70' }} mt-0.5">
                                        {{ $item->notes ?: 'Tanpa catatan' }}
                                    </p>
                                </td>
                                <td class="py-4 px-3 text-center font-medium text-teak tabular-nums">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-4 px-3 text-right text-taupe tabular-nums">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-4 pl-3 text-right font-bold text-teak tabular-nums">
                                    {{ $item->formatted_subtotal }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($order->notes)
                <div class="mt-4 rounded-xl bg-alabaster border border-sand px-4 py-3 text-xs text-taupe">
                    <strong class="text-teak">Catatan Pesanan:</strong> {{ $order->notes }}
                </div>
            @endif

            {{-- Totals Breakdown (Matches PDF Page 6 exact layout & math) --}}
            <div class="mt-5 pt-5 border-t border-sand space-y-2.5 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-taupe">Subtotal</span>
                    <span class="font-semibold text-teak tabular-nums">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-taupe">Diskon</span>
                    <span class="font-semibold text-teak tabular-nums">
                        {{ $order->discount > 0 ? '- Rp '.number_format($order->discount, 0, ',', '.') : 'Rp 0' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-taupe">Biaya Layanan (5%)</span>
                    <span class="font-semibold text-teak tabular-nums">Rp {{ number_format($order->service_charge, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-taupe">Pajak PB1 (10%)</span>
                    <span class="font-semibold text-teak tabular-nums">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                </div>

                <div class="pt-4 mt-3 border-t border-sand flex items-center justify-between">
                    <span class="text-base font-bold text-teak">Total Tagihan</span>
                    <span class="text-2xl font-bold text-copper tabular-nums">{{ $order->formatted_total }}</span>
                </div>

                <p class="text-[11px] text-taupe pt-1">
                    Harga tersimpan saat pemesanan (price snapshot). PB1 dihitung dari subtotal setelah diskon + biaya layanan.
                </p>
            </div>
        </div>

        {{-- Right 5 Columns: Pembayaran Panel --}}
        <div class="xl:col-span-5 bg-white rounded-lg border border-sand p-6 shadow-2xs">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-teak">Pembayaran</h3>
                <svg class="w-5 h-5 text-copper" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                </svg>
            </div>

            <div class="rounded-xl bg-alabaster border border-sand p-4 mb-5">
                <p class="text-xs text-taupe">Total yang harus dibayar</p>
                <p class="text-3xl font-bold text-copper tabular-nums mt-1">{{ $order->formatted_total }}</p>
            </div>

            @if ($order->isPaid())
                {{-- Already Paid Receipt Summary --}}
                <div class="space-y-4">
                    <div class="rounded-xl bg-sage-tint border border-sage/20 p-4">
                        <p class="text-sm font-bold text-sage">✓ Pembayaran Telah Lunas</p>
                        <p class="text-xs text-sage/85 mt-0.5">
                            Dikonfirmasi pada {{ ($order->completed_at ?? $order->updated_at)->translatedFormat('d F Y · H:i') }} WIB
                        </p>
                    </div>

                    @foreach ($order->payments as $payment)
                        <div class="rounded-xl border border-sand p-4 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-taupe">Metode Pembayaran</span>
                                <span class="font-bold text-teak">{{ $payment->method_label }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-taupe">Nominal Tagihan</span>
                                <span class="font-bold text-teak tabular-nums">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                            </div>
                            @if ($payment->method === 'cash')
                                <div class="flex justify-between">
                                    <span class="text-taupe">Uang Diterima</span>
                                    <span class="font-semibold text-teak tabular-nums">Rp {{ number_format((int) $payment->received_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-sand/60">
                                    <span class="font-semibold text-sage">Kembalian</span>
                                    <span class="font-bold text-sage tabular-nums">Rp {{ number_format($payment->change_amount, 0, ',', '.') }}</span>
                                </div>
                            @elseif ($payment->reference_number)
                                <div class="flex justify-between">
                                    <span class="text-taupe">No. Referensi</span>
                                    <span class="font-mono font-semibold text-teak">{{ $payment->reference_number }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif ($order->status === 'cancelled')
                <div class="rounded-xl bg-clay-tint border border-clay/25 p-4 text-xs text-clay">
                    <p class="font-bold text-sm mb-1">Pesanan Dibatalkan</p>
                    <p>Pesanan yang telah dibatalkan tidak memerlukan pembayaran.</p>
                </div>
            @else
                {{-- Interactive Cashier Payment Form (Matches PDF Page 6) --}}
                <form action="{{ route('orders.pay', $order->order_number) }}" method="POST" id="payment-form" class="no-print space-y-5">
                    @csrf
                    <input type="hidden" name="method" id="payment-method-input" value="{{ old('method', 'cash') }}">

                    <div>
                        <p class="text-xs font-bold text-teak mb-2.5">Pilih metode pembayaran</p>
                        <div class="grid grid-cols-2 gap-2.5" id="payment-method-buttons">
                            @php
                                $methodButtons = [
                                    ['value' => 'cash', 'label' => 'Tunai'],
                                    ['value' => 'qris', 'label' => 'QRIS'],
                                    ['value' => 'debit_card', 'label' => 'Debit/Kredit'],
                                    ['value' => 'e_wallet', 'label' => 'Dompet Digital'],
                                ];
                            @endphp
                            @foreach ($methodButtons as $btn)
                                <button type="button"
                                        data-method="{{ $btn['value'] }}"
                                        class="pay-method-btn px-3.5 py-3 rounded-xl border text-xs font-semibold transition cursor-pointer flex items-center justify-center gap-2">
                                    <span>{{ $btn['label'] }}</span>
                                </button>
                            @endforeach
                        </div>

                        {{-- Card Type Sub-Selector when Debit/Kredit is chosen --}}
                        <div id="card-subtype-box" class="hidden mt-2.5 flex items-center gap-2 text-xs">
                            <span class="text-taupe">Jenis kartu:</span>
                            <button type="button" data-card="debit_card" class="card-type-pill px-2.5 py-1 rounded-lg border border-copper bg-copper-tint text-copper font-semibold cursor-pointer">Kartu Debit</button>
                            <button type="button" data-card="credit_card" class="card-type-pill px-2.5 py-1 rounded-lg border border-sand bg-white text-taupe font-medium cursor-pointer">Kartu Kredit</button>
                        </div>
                    </div>

                    {{-- Cash Section: Uang Diterima + Quick Denominations + Kembalian --}}
                    <div id="cash-payment-fields" class="space-y-3.5">
                        <div>
                            <label for="received_amount" class="block text-xs font-bold text-teak mb-1.5">
                                Uang Diterima (Rp)
                            </label>
                            @php
                                $defaultCash = (int) (ceil($order->total / 50000) * 50000);
                            @endphp
                            <input type="number"
                                   id="received_amount"
                                   name="received_amount"
                                   min="{{ $order->total }}"
                                   step="500"
                                   value="{{ old('received_amount', $defaultCash) }}"
                                   class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm font-semibold text-teak tabular-nums focus:outline-none focus:border-copper">

                            {{-- Quick Cash Denomination Chips --}}
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <button type="button" data-cash="{{ $order->total }}" class="quick-cash-btn px-2.5 py-1 rounded-lg bg-alabaster border border-sand text-[11px] font-semibold text-teak hover:border-copper cursor-pointer">
                                    Uang Pas
                                </button>
                                @foreach ([50000, 100000, 200000, 250000, 300000] as $nom)
                                    @if ($nom >= $order->total)
                                        <button type="button" data-cash="{{ $nom }}" class="quick-cash-btn px-2.5 py-1 rounded-lg bg-alabaster border border-sand text-[11px] font-semibold text-teak hover:border-copper tabular-nums cursor-pointer">
                                            Rp {{ number_format($nom, 0, ',', '.') }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div id="change-box" class="rounded-xl bg-sage-tint border border-sage/20 px-4 py-3.5 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-sage">Kembalian</p>
                                <p class="text-[11px] text-sage/75" id="change-hint">Setelah pembayaran diterima</p>
                            </div>
                            <p class="text-lg font-bold text-sage tabular-nums" id="change-amount-display">Rp 0</p>
                        </div>
                    </div>

                    {{-- Dynamic QRIS Scan Card (Shown when QRIS is selected) --}}
                    <div id="qris-payment-box" class="hidden rounded-lg kn-batik-banner border border-brass/40 p-4 text-center space-y-3 text-alabaster">
                        <div class="flex items-center justify-between text-[10.5px] font-bold tracking-wider uppercase px-1">
                            <span class="text-brass">QRIS · Standar Pembayaran Nasional</span>
                            <span class="px-2 py-0.5 rounded bg-brass/15 border border-brass/35 text-brass text-[10px]">GPN</span>
                        </div>

                        <div class="bg-white rounded-lg border border-brass/30 p-3.5 inline-block shadow-sm">
                            <p class="font-serif text-sm font-bold text-teak">Kafe Nusantara</p>
                            <p class="text-[10px] text-taupe mb-2.5 tabular-nums">NMID: ID1026049281740 · {{ $order->order_number }}</p>
                            <x-qr-matrix :payload="'QRIS.KAFENUSANTARA.'.$order->order_number.'.'.$order->total" :size="168" />
                        </div>

                        <div class="flex items-center justify-center gap-2 text-xs font-semibold text-emerald-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 kn-pulse-dot"></span>
                            <span>Siap dipindai pelanggan · {{ $order->formatted_total }}</span>
                        </div>
                    </div>

                    {{-- Non-Cash Section: Reference Number --}}
                    <div id="noncash-payment-fields" class="hidden space-y-2">
                        <label for="reference_number" class="block text-xs font-bold text-teak">
                            Nomor Referensi Transaksi (Opsional)
                        </label>
                        <input type="text"
                               id="reference_number"
                               name="reference_number"
                               value="{{ old('reference_number') }}"
                               placeholder="Kosongkan untuk nomor referensi otomatis..."
                               class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-xs text-teak focus:outline-none focus:border-copper">
                        <p class="text-[11px] text-taupe">
                            Nominal tepat <strong>{{ $order->formatted_total }}</strong> akan ditagihkan tanpa uang kembalian.
                        </p>
                    </div>

                    <button type="submit"
                            id="submit-pay-btn"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition cursor-pointer">
                        <span>✓</span>
                        <span>Terima Pembayaran</span>
                    </button>

                    <p class="text-[11px] text-taupe leading-relaxed">
                        Pembayaran belum diterima. Status pesanan akan menjadi <strong>Lunas & Selesai</strong> dan meja otomatis kembali <strong>Tersedia</strong> setelah dikonfirmasi kasir.
                    </p>
                </form>
            @endif
        </div>
    </div>

    {{-- Reusable 80mm Receipt Markup (Used in On-Screen Preview Modal AND @media print) --}}
    @php
        $latestPayment = $order->payments->last();
    @endphp

    {{-- Print-Only 80mm Boutique Thermal Receipt --}}
    <div id="thermal-receipt-print" class="hidden kn-print-only mx-auto w-[310px] py-4 text-teak font-sans text-xs">
        <div class="text-center pb-3 border-b border-dashed border-teak/40">
            <p class="font-serif text-xl font-bold tracking-tight">Kafe Nusantara</p>
            <p class="text-[11px] text-taupe mt-0.5">Jl. Nusantara Raya No. 18, Jakarta</p>
            <p class="text-[10.5px] text-taupe">Telp (021) 555-0194 · NPWP 01.884.291.4-011</p>
        </div>

        <div class="py-2.5 border-b border-dashed border-teak/40 space-y-1 text-[11px]">
            <div class="flex justify-between"><span>No. Order</span><span class="font-bold tabular-nums">{{ $order->order_number }}</span></div>
            <div class="flex justify-between"><span>Waktu</span><span class="tabular-nums">{{ $order->created_at->format('d/m/Y H:i') }} WIB</span></div>
            <div class="flex justify-between"><span>Kasir</span><span>{{ $order->user?->name ?: 'Staf Gerai' }}</span></div>
            <div class="flex justify-between"><span>Pelanggan</span><span>{{ $customerDisplay }}</span></div>
            <div class="flex justify-between">
                <span>Layanan</span>
                <span class="font-semibold">{{ $order->type_label }}{{ $order->diningTable ? ' · Meja '.$order->diningTable->number : '' }}</span>
            </div>
        </div>

        <div class="py-2.5 border-b border-dashed border-teak/40 space-y-2">
            @foreach ($order->items as $item)
                <div>
                    <div class="flex justify-between font-semibold">
                        <span>{{ $item->menu?->name ?? 'Menu Terarsip' }}</span>
                        <span class="tabular-nums">{{ $item->formatted_subtotal }}</span>
                    </div>
                    <p class="text-[10.5px] text-taupe tabular-nums">
                        {{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        @if ($item->notes)
                            · <em>({{ $item->notes }})</em>
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        <div class="py-2.5 border-b border-dashed border-teak/40 space-y-1 text-[11px] tabular-nums">
            <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
            @if ($order->discount > 0)
                <div class="flex justify-between"><span>Diskon</span><span>- Rp {{ number_format($order->discount, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between"><span>Biaya Layanan (5%)</span><span>Rp {{ number_format($order->service_charge, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span>Pajak PB1 (10%)</span><span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span></div>
            <div class="flex justify-between text-sm font-bold pt-1.5 border-t border-teak/20">
                <span>TOTAL</span><span>{{ $order->formatted_total }}</span>
            </div>
        </div>

        <div class="py-2.5 border-b border-dashed border-teak/40 space-y-1 text-[11px] tabular-nums">
            @if ($order->isPaid() && $latestPayment)
                <div class="flex justify-between"><span>Metode Bayar</span><span class="font-bold uppercase">{{ $latestPayment->method_label }}</span></div>
                @if ($latestPayment->method === 'cash')
                    <div class="flex justify-between"><span>Tunai Diterima</span><span>Rp {{ number_format((int) $latestPayment->received_amount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between font-bold"><span>Kembalian</span><span>Rp {{ number_format($latestPayment->change_amount, 0, ',', '.') }}</span></div>
                @elseif ($latestPayment->reference_number)
                    <div class="flex justify-between"><span>Ref</span><span class="font-mono">{{ $latestPayment->reference_number }}</span></div>
                @endif
            @else
                <p class="text-center font-bold uppercase tracking-wider py-0.5">Tagihan Sementara · Belum Lunas</p>
            @endif
        </div>

        <div class="pt-3 text-center space-y-2">
            <x-qr-matrix :payload="'STRUK:'.$order->order_number" :size="76" />
            <p class="font-serif italic text-xs">Terima kasih telah singgah di Kafe Nusantara.</p>
            <p class="text-[10px] text-taupe">Cita rasa lokal · Layanan sepenuh hati</p>
        </div>
    </div>

    {{-- Interactive On-Screen 80mm Receipt Preview Modal --}}
    <div id="receipt-preview-modal"
         onclick="if (event.target === this) this.classList.add('hidden')"
         class="no-print hidden fixed inset-0 z-50 bg-teak/55 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="w-full max-w-sm rounded-2xl bg-alabaster border border-sand shadow-2xl p-5 kn-enter">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-copper">Pratinjau Struk Kasir (80mm)</span>
                <button type="button"
                        onclick="document.getElementById('receipt-preview-modal').classList.add('hidden')"
                        class="w-7 h-7 rounded-lg bg-white border border-sand text-taupe hover:text-teak text-xs font-bold cursor-pointer">
                    ✕
                </button>
            </div>

            {{-- Boutique Paper Ticket Card --}}
            <div class="bg-white rounded-xl border border-sand shadow-sm px-5 py-5 text-xs text-teak max-h-[68vh] overflow-y-auto">
                <div class="text-center pb-3 border-b border-dashed border-sand">
                    <p class="font-serif text-xl font-bold text-teak">Kafe Nusantara</p>
                    <p class="text-[11px] text-taupe mt-0.5">Jl. Nusantara Raya No. 18, Jakarta</p>
                    <p class="text-[10.5px] text-taupe">Telp (021) 555-0194</p>
                </div>

                <div class="py-3 border-b border-dashed border-sand space-y-1 text-[11px]">
                    <div class="flex justify-between"><span class="text-taupe">No. Order</span><span class="font-bold tabular-nums">{{ $order->order_number }}</span></div>
                    <div class="flex justify-between"><span class="text-taupe">Waktu</span><span class="tabular-nums">{{ $order->created_at->format('d/m/Y H:i') }} WIB</span></div>
                    <div class="flex justify-between"><span class="text-taupe">Kasir</span><span>{{ $order->user?->name ?: 'Staf Gerai' }}</span></div>
                    <div class="flex justify-between"><span class="text-taupe">Pelanggan</span><span class="font-semibold">{{ $customerDisplay }}</span></div>
                    <div class="flex justify-between">
                        <span class="text-taupe">Layanan</span>
                        <span class="font-semibold">{{ $order->type_label }}{{ $order->diningTable ? ' · Meja '.$order->diningTable->number : '' }}</span>
                    </div>
                </div>

                <div class="py-3 border-b border-dashed border-sand space-y-2">
                    @foreach ($order->items as $item)
                        <div>
                            <div class="flex justify-between font-semibold">
                                <span>{{ $item->menu?->name ?? 'Menu Terarsip' }}</span>
                                <span class="tabular-nums">{{ $item->formatted_subtotal }}</span>
                            </div>
                            <p class="text-[10.5px] text-taupe tabular-nums">
                                {{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                @if ($item->notes)
                                    <span class="text-copper">· {{ $item->notes }}</span>
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="py-3 border-b border-dashed border-sand space-y-1 text-[11px] tabular-nums">
                    <div class="flex justify-between"><span class="text-taupe">Subtotal</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                    @if ($order->discount > 0)
                        <div class="flex justify-between text-copper"><span>Diskon</span><span>- Rp {{ number_format($order->discount, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-taupe">Biaya Layanan (5%)</span><span>Rp {{ number_format($order->service_charge, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-taupe">Pajak PB1 (10%)</span><span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-sm font-bold text-copper pt-2 border-t border-sand">
                        <span class="text-teak">TOTAL</span><span>{{ $order->formatted_total }}</span>
                    </div>
                </div>

                <div class="py-2.5 border-b border-dashed border-sand space-y-1 text-[11px] tabular-nums">
                    @if ($order->isPaid() && $latestPayment)
                        <div class="flex justify-between"><span class="text-taupe">Pembayaran</span><span class="font-bold text-sage uppercase">{{ $latestPayment->method_label }} · LUNAS</span></div>
                        @if ($latestPayment->method === 'cash')
                            <div class="flex justify-between"><span class="text-taupe">Diterima</span><span>Rp {{ number_format((int) $latestPayment->received_amount, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between font-bold text-sage"><span>Kembalian</span><span>Rp {{ number_format($latestPayment->change_amount, 0, ',', '.') }}</span></div>
                        @elseif ($latestPayment->reference_number)
                            <div class="flex justify-between"><span class="text-taupe">No. Ref</span><span class="font-mono">{{ $latestPayment->reference_number }}</span></div>
                        @endif
                    @else
                        <p class="text-center font-bold text-saffron uppercase tracking-wider py-0.5">Tagihan Sementara · Belum Dibayar</p>
                    @endif
                </div>

                <div class="pt-3 text-center space-y-1.5">
                    <x-qr-matrix :payload="'STRUK:'.$order->order_number" :size="76" />
                    <p class="font-serif italic text-xs text-teak">Terima kasih telah singgah di Kafe Nusantara.</p>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-2.5">
                <button type="button"
                        onclick="document.getElementById('receipt-preview-modal').classList.add('hidden')"
                        class="px-4 py-2.5 rounded-xl bg-white border border-sand text-xs font-semibold text-teak hover:bg-sand-soft cursor-pointer">
                    Tutup
                </button>
                <button type="button"
                        onclick="window.print()"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-xs font-bold shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>
                    </svg>
                    <span>Cetak ke Printer</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@if (! $order->isPaid() && $order->status !== 'cancelled')
@push('scripts')
<script>
    (function () {
        const orderTotal = {{ (int) $order->total }};
        const methodInput = document.getElementById('payment-method-input');
        const methodBtns = document.querySelectorAll('.pay-method-btn');
        const cardSubtypeBox = document.getElementById('card-subtype-box');
        const cardTypePills = document.querySelectorAll('.card-type-pill');
        const cashFields = document.getElementById('cash-payment-fields');
        const qrisBox = document.getElementById('qris-payment-box');
        const nonCashFields = document.getElementById('noncash-payment-fields');
        const receivedInput = document.getElementById('received_amount');
        const changeDisplay = document.getElementById('change-amount-display');
        const changeBox = document.getElementById('change-box');
        const changeHint = document.getElementById('change-hint');
        const submitBtn = document.getElementById('submit-pay-btn');

        function formatRp(num) {
            return 'Rp ' + Math.max(0, Math.round(num)).toLocaleString('id-ID');
        }

        function updateCashChange() {
            if (methodInput.value !== 'cash') {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                return;
            }
            const received = parseInt(receivedInput.value || '0', 10);
            const diff = received - orderTotal;

            if (diff < 0) {
                changeDisplay.textContent = '- ' + formatRp(Math.abs(diff));
                changeHint.textContent = 'Nominal kurang dari total tagihan';
                changeBox.className = 'rounded-xl bg-clay-tint border border-clay/25 px-4 py-3.5 flex items-center justify-between text-clay';
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                changeDisplay.textContent = formatRp(diff);
                changeHint.textContent = 'Setelah pembayaran diterima';
                changeBox.className = 'rounded-xl bg-sage-tint border border-sage/20 px-4 py-3.5 flex items-center justify-between text-sage';
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        function setMethod(method) {
            methodInput.value = method;
            const isCard = method === 'debit_card' || method === 'credit_card';
            const isCash = method === 'cash';

            methodBtns.forEach(btn => {
                const btnMethod = btn.dataset.method;
                const active = btnMethod === method || (btnMethod === 'debit_card' && isCard);
                btn.className = active
                    ? 'pay-method-btn px-3.5 py-3 rounded-xl border-2 border-copper bg-copper-tint text-copper text-xs font-bold transition cursor-pointer flex items-center justify-center gap-2'
                    : 'pay-method-btn px-3.5 py-3 rounded-xl border border-sand bg-white text-teak hover:border-copper/50 text-xs font-semibold transition cursor-pointer flex items-center justify-center gap-2';
            });

            cardSubtypeBox.classList.toggle('hidden', !isCard);
            cashFields.classList.toggle('hidden', !isCash);
            qrisBox?.classList.toggle('hidden', method !== 'qris');
            nonCashFields.classList.toggle('hidden', isCash);
            receivedInput.required = isCash;

            updateCashChange();
        }

        methodBtns.forEach(btn => {
            btn.addEventListener('click', () => setMethod(btn.dataset.method));
        });

        cardTypePills.forEach(pill => {
            pill.addEventListener('click', () => {
                const cardVal = pill.dataset.card;
                methodInput.value = cardVal;
                cardTypePills.forEach(p => {
                    const active = p.dataset.card === cardVal;
                    p.className = active
                        ? 'card-type-pill px-2.5 py-1 rounded-lg border border-copper bg-copper-tint text-copper font-semibold cursor-pointer'
                        : 'card-type-pill px-2.5 py-1 rounded-lg border border-sand bg-white text-taupe font-medium cursor-pointer';
                });
            });
        });

        document.querySelectorAll('.quick-cash-btn').forEach(chip => {
            chip.addEventListener('click', () => {
                receivedInput.value = chip.dataset.cash;
                updateCashChange();
            });
        });

        receivedInput?.addEventListener('input', updateCashChange);
        setMethod(methodInput.value || 'cash');
    })();
</script>
@endpush
@endif
