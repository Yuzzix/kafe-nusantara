@extends('layouts.app')

@section('title', 'Buat Pesanan Baru')
@section('breadcrumb')
    <a href="{{ route('orders.index') }}" class="hover:text-copper">Beranda / Pesanan & Kasir</a> / Transaksi Baru
@endsection
@section('page_title', 'Buat Pesanan Baru')

@section('header_actions')
    <a href="{{ route('orders.index') }}"
       class="px-3 py-2 rounded-lg border border-sand bg-white text-xs font-semibold text-teak hover:bg-alabaster transition">
        Batal
    </a>
    <button type="submit"
            form="pos-order-form"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition cursor-pointer">
        <span>Simpan & Kirim ke Dapur</span>
    </button>
@endsection

@section('content')
    <form id="pos-order-form" action="{{ route('orders.store') }}" method="POST">
        @csrf
        <input type="hidden" name="order_type" id="order-type-input" value="{{ old('order_type', 'dine_in') }}">
        <input type="hidden" name="dining_table_id" id="dining-table-input" value="{{ old('dining_table_id', $preselectedTableId) }}">

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
            {{-- Left 8 Columns: Customer, Order Type, Table Picker, & Menu Selector --}}
            <div class="xl:col-span-8 space-y-5">
                {{-- Card 1: Customer Name, Order Type Toggle, & Available Table Cards --}}
                <div class="bg-white rounded-lg border border-sand p-5 space-y-4 shadow-2xs">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="block text-xs font-bold text-teak mb-1.5">
                                Nama Pelanggan
                            </label>
                            <input type="text"
                                   id="customer_name"
                                   name="customer_name"
                                   value="{{ old('customer_name') }}"
                                   placeholder="Contoh: Dewi (opsional)"
                                   class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak placeholder:text-taupe/60 focus:outline-none focus:border-copper">
                        </div>

                        <div>
                            <span class="block text-xs font-bold text-teak mb-1.5">Tipe Pesanan</span>
                            <div class="grid grid-cols-2 gap-1.5 rounded-xl bg-alabaster border border-sand p-1">
                                <button type="button"
                                        data-order-type="dine_in"
                                        class="order-type-btn py-2 px-3 rounded-lg text-xs font-bold transition cursor-pointer">
                                    Makan di Tempat
                                </button>
                                <button type="button"
                                        data-order-type="takeaway"
                                        class="order-type-btn py-2 px-3 rounded-lg text-xs font-bold transition cursor-pointer">
                                    Bawa Pulang
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Available Tables Grid (Shown when Makan di Tempat is active) --}}
                    <div id="table-picker-section">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs font-bold text-teak">Pilih Meja Tersedia</span>
                            <span class="text-[11px] text-taupe">{{ $availableTables->count() }} meja siap ditempati</span>
                        </div>

                        @if ($availableTables->isEmpty())
                            <div class="rounded-xl bg-clay-tint border border-clay/25 p-4 text-xs text-clay">
                                Seluruh meja sedang terisi/dipesan saat ini. Silakan pilih tipe <strong>Bawa Pulang</strong> atau selesaikan pembayaran meja yang sudah selesai makan.
                            </div>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                @foreach ($availableTables as $table)
                                    @php
                                        $areaLabel = match ($table->area) {
                                            'Indoor' => 'Dalam ruangan',
                                            'Outdoor' => 'Luar ruangan',
                                            default => 'Ruang VIP',
                                        };
                                    @endphp
                                    <button type="button"
                                            data-table-id="{{ $table->id }}"
                                            data-table-number="{{ $table->number }}"
                                            class="table-card-btn text-left rounded-xl border p-3.5 transition cursor-pointer relative">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold">{{ $table->number }}</span>
                                            <span class="table-check-icon hidden text-copper">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                    <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-taupe mt-1">{{ $areaLabel }} · {{ $table->capacity }} kursi</p>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card 2: Interactive Menu Selector --}}
                <div class="bg-white rounded-lg border border-sand p-5 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <h3 class="text-base font-bold text-teak">Pilih Menu</h3>

                        <div class="relative w-full sm:w-64">
                            <svg class="w-4 h-4 text-taupe/70 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
                            </svg>
                            <input type="text"
                                   id="pos-menu-search"
                                   placeholder="Cari menu..."
                                   class="w-full pl-9 pr-4 py-2 rounded-xl bg-alabaster border border-sand text-xs text-teak placeholder:text-taupe/65 focus:outline-none focus:border-copper">
                        </div>
                    </div>

                    {{-- Category Filter Pills --}}
                    <div class="flex flex-wrap gap-2 mb-5">
                        <button type="button"
                                data-cat-filter="all"
                                class="cat-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold bg-copper-tint text-copper border border-copper/30 cursor-pointer">
                            Semua
                        </button>
                        @foreach ($categories as $category)
                            <button type="button"
                                    data-cat-filter="{{ $category->slug }}"
                                    class="cat-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-alabaster text-taupe border border-sand hover:text-teak cursor-pointer">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Menu Rows with +/- Steppers & Per-Item Notes --}}
                    <div class="space-y-3 max-h-[540px] overflow-y-auto pr-1" id="pos-menu-list">
                        @foreach ($categories as $category)
                            @foreach ($category->availableMenus as $menu)
                                @php
                                    $oldQty = (int) old("items.{$menu->id}.quantity", 0);
                                    $oldNotes = old("items.{$menu->id}.notes", '');
                                @endphp
                                <div class="pos-menu-row rounded-lg border p-3.5 transition"
                                     data-menu-id="{{ $menu->id }}"
                                     data-menu-name="{{ $menu->name }}"
                                     data-menu-search="{{ mb_strtolower($menu->name.' '.$category->name) }}"
                                     data-menu-cat="{{ $category->slug }}"
                                     data-menu-price="{{ $menu->price }}">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3.5 min-w-0">
                                            @if ($menu->image_url)
                                                <div class="w-12 h-12 rounded-xl overflow-hidden border border-sand bg-sand-soft shrink-0">
                                                    <img src="{{ $menu->image_url }}"
                                                         alt="{{ $menu->name }}"
                                                         loading="lazy"
                                                         decoding="async"
                                                         class="w-full h-full max-w-none object-cover">
                                                </div>
                                            @else
                                                <x-category-icon :slug="$category->slug" size="sm" />
                                            @endif

                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm font-bold text-teak truncate">{{ $menu->name }}</p>
                                                    @if ($menu->is_featured)
                                                        <x-status-badge tone="brass" label="Best Seller" />
                                                    @endif
                                                </div>
                                                <p class="text-xs font-semibold text-copper tabular-nums mt-0.5">
                                                    {{ $menu->formatted_price }}
                                                    <span class="font-normal text-taupe">· {{ $category->name }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Quantity Stepper --}}
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button"
                                                    data-step="-1"
                                                    class="qty-step-btn w-8 h-8 rounded-lg border border-sand bg-white text-teak font-bold hover:border-copper flex items-center justify-center cursor-pointer">
                                                −
                                            </button>
                                            <input type="number"
                                                   name="items[{{ $menu->id }}][quantity]"
                                                   value="{{ $oldQty }}"
                                                   min="0"
                                                   max="99"
                                                   readonly
                                                   class="menu-qty-input w-9 text-center text-sm font-bold text-teak bg-transparent tabular-nums focus:outline-none">
                                            <button type="button"
                                                    data-step="1"
                                                    class="qty-step-btn w-8 h-8 rounded-lg bg-copper hover:bg-copper-dark text-white font-bold flex items-center justify-center cursor-pointer">
                                                +
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Per-Item Note Input (Shown automatically when qty > 0) --}}
                                    <div class="item-note-wrapper {{ $oldQty > 0 ? '' : 'hidden' }} mt-3 pt-3 border-t border-sand/60">
                                        <div class="relative">
                                            <svg class="w-3.5 h-3.5 text-taupe absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            </svg>
                                            <input type="text"
                                                   name="items[{{ $menu->id }}][notes]"
                                                   value="{{ $oldNotes }}"
                                                   placeholder="Tambahkan catatan per item (misal: Kurangi gula, Tidak pedas)..."
                                                   class="menu-note-input w-full rounded-xl bg-alabaster border border-sand pl-8 pr-3 py-2 text-xs text-teak placeholder:text-taupe/65 focus:outline-none focus:border-copper">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="mt-4 pt-3 border-t border-sand/70 flex flex-wrap items-center justify-between gap-2 text-xs text-taupe">
                        <span id="selected-counter-text">0 menu dipilih · 0 porsi</span>
                        <span>Harga per porsi ditampilkan (otomatis disimpan sebagai price snapshot)</span>
                    </div>
                </div>
            </div>

            {{-- Right 4 Columns: Live Ringkasan Pesanan Sticky Card --}}
            <div class="xl:col-span-4 xl:sticky xl:top-5 bg-white rounded-lg border border-sand p-5 space-y-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-teak">Ringkasan Pesanan</h3>
                        <p class="text-xs font-semibold text-taupe mt-0.5" id="summary-meta-line">Tanpa nama · Pilih meja</p>
                    </div>
                    <x-status-badge tone="saffron" label="Draf" />
                </div>

                {{-- Selected Items Live List --}}
                <div id="summary-items-container" class="border-t border-b border-sand py-4 space-y-3 max-h-64 overflow-y-auto">
                    <p class="text-xs text-taupe text-center py-4" id="summary-empty-state">
                        Belum ada sajian dipilih. Klik tombol <strong>+</strong> pada daftar menu di samping.
                    </p>
                </div>

                {{-- Optional Order Discount & Order Note --}}
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex items-center justify-between gap-3">
                        <label for="discount-input" class="text-xs font-medium text-taupe">Diskon (Rp)</label>
                        <input type="number"
                               id="discount-input"
                               name="discount"
                               min="0"
                               step="1000"
                               value="{{ old('discount', 0) }}"
                               class="w-32 rounded-lg border border-sand bg-alabaster px-3 py-1.5 text-right text-xs font-semibold text-teak tabular-nums focus:outline-none focus:border-copper">
                    </div>
                    <div>
                        <input type="text"
                               name="notes"
                               value="{{ old('notes') }}"
                               placeholder="Catatan umum pesanan (opsional)..."
                               class="w-full rounded-xl border border-sand bg-alabaster px-3 py-2 text-xs text-teak placeholder:text-taupe/65 focus:outline-none focus:border-copper">
                    </div>
                </div>

                {{-- Live Calculation Breakdown --}}
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-taupe">Subtotal</span>
                        <span class="font-semibold text-teak tabular-nums" id="summary-subtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-taupe">Diskon</span>
                        <span class="font-semibold text-teak tabular-nums" id="summary-discount">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-taupe" id="summary-service-label">Biaya Layanan (5%)</span>
                        <span class="font-semibold text-teak tabular-nums" id="summary-service">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-taupe">Pajak PB1 (10%)</span>
                        <span class="font-semibold text-teak tabular-nums" id="summary-tax">Rp 0</span>
                    </div>

                    <p class="text-[11px] text-taupe pt-0.5" id="summary-formula-note">
                        PB1: 10% × (subtotal setelah diskon + biaya layanan)
                    </p>

                    <div class="pt-3 mt-2 border-t border-sand flex items-center justify-between">
                        <span class="text-base font-bold text-teak">Total</span>
                        <span class="text-2xl font-bold text-copper tabular-nums" id="summary-total">Rp 0</span>
                    </div>
                </div>

                {{-- Table Status Note Box (Matches PDF Page 7) --}}
                <div class="rounded-xl bg-saffron-tint/80 border border-saffron/20 p-3.5 text-xs">
                    <p class="font-bold text-saffron" id="summary-table-box-title">Meja belum ditempati</p>
                    <p class="text-taupe mt-0.5 leading-relaxed" id="summary-table-box-desc">
                        Meja tetap tersedia hingga pesanan disimpan. Draf ini belum dikirim ke dapur.
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-1">
                    <a href="{{ route('orders.index') }}"
                       class="px-4 py-2.5 rounded-xl border border-sand bg-white text-sm font-semibold text-teak hover:bg-alabaster transition">
                        Batal
                    </a>
                    <button type="submit"
                            id="summary-submit-btn"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition cursor-pointer">
                        <span>✓</span>
                        <span>Simpan Pesanan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const SERVICE_RATE = {{ $serviceRate }};
        const TAX_RATE = {{ $taxRate }};

        const orderTypeInput = document.getElementById('order-type-input');
        const tableInput = document.getElementById('dining-table-input');
        const customerInput = document.getElementById('customer_name');
        const discountInput = document.getElementById('discount-input');
        const tableSection = document.getElementById('table-picker-section');

        const orderTypeBtns = document.querySelectorAll('.order-type-btn');
        const tableBtns = document.querySelectorAll('.table-card-btn');
        const catBtns = document.querySelectorAll('.cat-tab-btn');
        const searchInput = document.getElementById('pos-menu-search');
        const menuRows = document.querySelectorAll('.pos-menu-row');

        const summaryMeta = document.getElementById('summary-meta-line');
        const summaryItems = document.getElementById('summary-items-container');
        const selectedCounter = document.getElementById('selected-counter-text');
        const subtotalEl = document.getElementById('summary-subtotal');
        const discountEl = document.getElementById('summary-discount');
        const serviceEl = document.getElementById('summary-service');
        const serviceLabelEl = document.getElementById('summary-service-label');
        const taxEl = document.getElementById('summary-tax');
        const formulaNoteEl = document.getElementById('summary-formula-note');
        const totalEl = document.getElementById('summary-total');
        const tableBoxTitle = document.getElementById('summary-table-box-title');
        const tableBoxDesc = document.getElementById('summary-table-box-desc');

        let activeCat = 'all';

        function formatRp(val) {
            return 'Rp ' + Math.round(val).toLocaleString('id-ID');
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function getSelectedTableNumber() {
            const activeBtn = Array.from(tableBtns).find(b => b.dataset.tableId === tableInput.value);
            return activeBtn ? activeBtn.dataset.tableNumber : null;
        }

        function refreshOrderTypeUI() {
            const isDineIn = orderTypeInput.value === 'dine_in';

            orderTypeBtns.forEach(btn => {
                const active = btn.dataset.orderType === orderTypeInput.value;
                btn.className = active
                    ? 'order-type-btn py-2 px-3 rounded-lg text-xs font-bold bg-white text-copper shadow-2xs border border-copper/25 transition cursor-pointer'
                    : 'order-type-btn py-2 px-3 rounded-lg text-xs font-semibold text-taupe hover:text-teak transition cursor-pointer';
            });

            tableSection.classList.toggle('hidden', !isDineIn);
            refreshTableUI();
            recalculateSummary();
        }

        function refreshTableUI() {
            tableBtns.forEach(btn => {
                const active = btn.dataset.tableId === tableInput.value;
                const check = btn.querySelector('.table-check-icon');
                btn.className = active
                    ? 'table-card-btn text-left rounded-xl border-2 border-copper bg-copper-tint p-3.5 transition cursor-pointer relative text-copper'
                    : 'table-card-btn text-left rounded-xl border border-sand bg-white hover:border-copper/40 p-3.5 transition cursor-pointer relative text-teak';
                if (check) check.classList.toggle('hidden', !active);
            });
        }

        function filterMenus() {
            const term = (searchInput.value || '').trim().toLowerCase();
            menuRows.forEach(row => {
                const matchCat = activeCat === 'all' || row.dataset.menuCat === activeCat;
                const matchSearch = term === '' || row.dataset.menuSearch.includes(term);
                row.classList.toggle('hidden', !(matchCat && matchSearch));
            });
        }

        function recalculateSummary() {
            const isDineIn = orderTypeInput.value === 'dine_in';
            const customer = (customerInput.value || '').trim() || 'Tanpa nama';
            const tableNum = getSelectedTableNumber();

            summaryMeta.textContent = isDineIn
                ? `${customer} · ${tableNum ? tableNum : 'Pilih meja'}`
                : `${customer} · Bawa Pulang`;

            if (isDineIn) {
                tableBoxTitle.textContent = tableNum ? `Meja ${tableNum} belum ditempati` : 'Pilih meja tersedia';
                tableBoxDesc.textContent = tableNum
                    ? `${tableNum} tetap tersedia hingga pesanan disimpan. Draf ini belum dikirim ke dapur.`
                    : 'Klik salah satu kartu meja yang tersedia di sebelah kiri.';
            } else {
                tableBoxTitle.textContent = 'Pesanan Bawa Pulang';
                tableBoxDesc.textContent = 'Tidak menggunakan meja dan bebas biaya layanan 5%.';
            }

            let subtotal = 0;
            let distinctCount = 0;
            let totalPortions = 0;
            const linesHtml = [];

            menuRows.forEach(row => {
                const qtyInput = row.querySelector('.menu-qty-input');
                const noteWrap = row.querySelector('.item-note-wrapper');
                const noteInput = row.querySelector('.menu-note-input');
                const qty = Math.max(0, parseInt(qtyInput.value || '0', 10));
                const price = parseInt(row.dataset.menuPrice, 10);
                const name = row.dataset.menuName;

                if (qty > 0) {
                    row.classList.add('border-copper/55', 'bg-copper-tint/20');
                    row.classList.remove('border-sand', 'bg-white');
                    noteWrap.classList.remove('hidden');

                    distinctCount++;
                    totalPortions += qty;
                    const lineTotal = qty * price;
                    subtotal += lineTotal;

                    const noteVal = (noteInput.value || '').trim();
                    const noteHtml = noteVal
                        ? `<p class="text-[11px] text-copper mt-0.5">Catatan: ${escapeHtml(noteVal)}</p>`
                        : '';

                    linesHtml.push(`
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <div class="min-w-0">
                                <p class="font-semibold text-teak">${qty} × ${escapeHtml(name)}</p>
                                ${noteHtml}
                            </div>
                            <span class="font-bold text-teak tabular-nums shrink-0">${formatRp(lineTotal)}</span>
                        </div>
                    `);
                } else {
                    row.classList.remove('border-copper/55', 'bg-copper-tint/20');
                    row.classList.add('border-sand', 'bg-white');
                    noteWrap.classList.add('hidden');
                }
            });

            selectedCounter.textContent = `${distinctCount} menu dipilih · ${totalPortions} porsi`;

            if (linesHtml.length === 0) {
                summaryItems.innerHTML = `
                    <p class="text-xs text-taupe text-center py-4">
                        Belum ada sajian dipilih. Klik tombol <strong>+</strong> pada daftar menu di samping.
                    </p>
                `;
            } else {
                summaryItems.innerHTML = linesHtml.join('');
            }

            const rawDiscount = Math.max(0, parseInt(discountInput.value || '0', 10));
            const discount = Math.min(rawDiscount, subtotal);
            const base = subtotal - discount;
            const service = isDineIn ? Math.round(base * SERVICE_RATE) : 0;
            const taxBase = base + service;
            const tax = Math.round(taxBase * TAX_RATE);
            const total = taxBase + tax;

            subtotalEl.textContent = formatRp(subtotal);
            discountEl.textContent = discount > 0 ? '- ' + formatRp(discount) : 'Rp 0';
            serviceLabelEl.textContent = isDineIn ? 'Biaya Layanan (5%)' : 'Biaya Layanan (Bawa Pulang)';
            serviceEl.textContent = formatRp(service);
            taxEl.textContent = formatRp(tax);
            formulaNoteEl.textContent = `PB1: 10% × ${formatRp(taxBase)} (subtotal + layanan)`;
            totalEl.textContent = formatRp(total);
        }

        // Event bindings
        orderTypeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                orderTypeInput.value = btn.dataset.orderType;
                refreshOrderTypeUI();
            });
        });

        tableBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tableInput.value = btn.dataset.tableId;
                refreshTableUI();
                recalculateSummary();
            });
        });

        catBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                activeCat = btn.dataset.catFilter;
                catBtns.forEach(b => {
                    const active = b.dataset.catFilter === activeCat;
                    b.className = active
                        ? 'cat-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold bg-copper-tint text-copper border border-copper/30 cursor-pointer'
                        : 'cat-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-alabaster text-taupe border border-sand hover:text-teak cursor-pointer';
                });
                filterMenus();
            });
        });

        searchInput?.addEventListener('input', filterMenus);
        customerInput?.addEventListener('input', recalculateSummary);
        discountInput?.addEventListener('input', recalculateSummary);

        menuRows.forEach(row => {
            const qtyInput = row.querySelector('.menu-qty-input');
            const noteInput = row.querySelector('.menu-note-input');
            row.querySelectorAll('.qty-step-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const delta = parseInt(btn.dataset.step, 10);
                    const next = Math.max(0, Math.min(99, parseInt(qtyInput.value || '0', 10) + delta));
                    qtyInput.value = next;
                    recalculateSummary();
                });
            });
            noteInput?.addEventListener('input', recalculateSummary);
        });

        refreshOrderTypeUI();
    })();
</script>
@endpush
