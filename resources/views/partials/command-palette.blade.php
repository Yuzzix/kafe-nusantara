{{-- Ctrl+K Quick Command Palette Modal --}}
<div id="kn-cmd-backdrop"
     class="no-print hidden fixed inset-0 z-50 bg-teak/50 backdrop-blur-xs flex items-start justify-center pt-[11vh] px-4"
     role="dialog"
     aria-modal="true"
     aria-label="Pintasan Cepat Kafe Nusantara">
    <div id="kn-cmd-dialog"
         class="w-full max-w-xl rounded-2xl bg-white border border-sand shadow-2xl overflow-hidden kn-enter">
        {{-- Search Input Header --}}
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-sand bg-alabaster/70">
            <svg class="w-4 h-4 text-copper shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
            </svg>
            <input type="text"
                   id="kn-cmd-input"
                   autocomplete="off"
                   placeholder="Ketik halaman, aksi kasir, nomor meja, atau nama sajian..."
                   class="flex-1 bg-transparent text-sm text-teak placeholder:text-taupe/65 focus:outline-none">
            <button type="button"
                    id="kn-cmd-close"
                    class="px-2 py-0.5 rounded-md bg-white border border-sand text-[10.5px] font-bold text-taupe hover:text-teak cursor-pointer">
                ESC
            </button>
        </div>

        {{-- Command Results List --}}
        <div id="kn-cmd-list" class="max-h-80 overflow-y-auto p-2 space-y-1"></div>

        {{-- Footer Hints --}}
        <div class="px-4 py-2.5 border-t border-sand bg-alabaster/60 flex items-center justify-between text-[11px] text-taupe">
            <div class="flex items-center gap-3">
                <span><strong class="text-teak">↑↓</strong> Pilih</span>
                <span><strong class="text-teak">↵</strong> Buka</span>
                <span><strong class="text-teak">Esc</strong> Tutup</span>
            </div>
            <span class="font-semibold text-teak">Pintasan POS</span>
        </div>
    </div>
</div>

@php
    $cmdItems = array_values(array_filter([
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canAccessOrders())
            ? ['title' => 'Buat Pesanan Baru', 'group' => 'Aksi Cepat', 'hint' => 'Transaksi baru', 'url' => route('orders.create'), 'keywords' => 'order baru pos kasir tambah pesanan meja takeaway']
            : null,
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canViewKitchen())
            ? ['title' => 'Buka Layar Dapur (Kitchen Display)', 'group' => 'Aksi Cepat', 'hint' => 'Antrean masak', 'url' => route('kitchen.index'), 'keywords' => 'dapur kds kitchen masak antrean saji']
            : null,
        ['title' => 'Buka Buku Menu Digital Tamu', 'group' => 'Pengalaman Tamu', 'hint' => 'Tampilan QR pelanggan', 'url' => route('menu-book'), 'keywords' => 'buku menu tamu digital customer qr'],
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canManageCatalog())
            ? ['title' => 'Tambah Sajian / Menu Baru', 'group' => 'Aksi Cepat', 'hint' => 'Form katalog', 'url' => route('menus.create'), 'keywords' => 'tambah menu baru makanan minuman foto']
            : null,
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canManageCatalog())
            ? ['title' => 'Tambah Kategori Menu', 'group' => 'Aksi Cepat', 'hint' => 'Kelompok baru', 'url' => route('categories.create'), 'keywords' => 'tambah kategori baru']
            : null,
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canViewDashboard())
            ? ['title' => 'Ringkasan Gerai', 'group' => 'Navigasi', 'hint' => 'KPI & antrean aktif', 'url' => route('dashboard'), 'keywords' => 'dashboard ringkasan gerai omzet laporan terlaris']
            : null,
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canManageCatalog())
            ? ['title' => 'Kategori Menu', 'group' => 'Navigasi', 'hint' => 'Master-Detail kategori', 'url' => route('categories.index'), 'keywords' => 'kategori makanan kopi camilan dessert']
            : null,
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canViewMenuCatalog())
            ? ['title' => 'Daftar Menu & Stok Harian', 'group' => 'Navigasi', 'hint' => '36 sajian katalog', 'url' => route('menus.index'), 'keywords' => 'daftar menu katalog harga tersedia habis stok']
            : null,
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canAccessOrders())
            ? ['title' => 'Pesanan & Kasir', 'group' => 'Navigasi', 'hint' => 'Daftar transaksi', 'url' => route('orders.index'), 'keywords' => 'pesanan kasir bayar lunas struk']
            : null,
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canManageTables())
            ? ['title' => 'Status Meja & QR Area', 'group' => 'Navigasi', 'hint' => 'Indoor, Outdoor, & VIP', 'url' => route('tables.index'), 'keywords' => 'status meja vip indoor outdoor reservasi']
            : null,
        (! isset($activeStaff) || ! $activeStaff || $activeStaff->canProcessPayment())
            ? ['title' => 'Unduh Laporan Transaksi (CSV)', 'group' => 'Laporan', 'hint' => 'Ekspor Excel/CSV', 'url' => route('orders.export'), 'keywords' => 'unduh ekspor csv excel laporan']
            : null,
    ]));
@endphp

<script>
    (function () {
        const COMMANDS = @json($cmdItems);

        const SEARCH_BASE = @json(route('workspace.search'));
        const backdrop = document.getElementById('kn-cmd-backdrop');
        const input = document.getElementById('kn-cmd-input');
        const listEl = document.getElementById('kn-cmd-list');
        const closeBtn = document.getElementById('kn-cmd-close');
        if (!backdrop || !input || !listEl) return;

        let visibleItems = [];
        let activeIndex = 0;

        function escapeHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function renderList() {
            const term = (input.value || '').trim();
            const q = term.toLowerCase();

            visibleItems = COMMANDS.filter(cmd => {
                if (!q) return true;
                return cmd.title.toLowerCase().includes(q)
                    || cmd.group.toLowerCase().includes(q)
                    || cmd.keywords.includes(q);
            });

            if (term.length > 0) {
                visibleItems.push({
                    title: `Cari "${term}" di seluruh pesanan, menu, & kategori`,
                    group: 'Pencarian Ruang Kerja',
                    hint: 'Hasil lengkap →',
                    url: `${SEARCH_BASE}?q=${encodeURIComponent(term)}`,
                });
            }

            if (activeIndex >= visibleItems.length) activeIndex = 0;

            listEl.innerHTML = visibleItems.map((item, idx) => {
                const active = idx === activeIndex;
                return `
                    <a href="${item.url}"
                       data-cmd-idx="${idx}"
                       class="kn-cmd-item flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm transition ${active ? 'bg-copper text-white shadow-xs' : 'text-teak hover:bg-alabaster'}">
                        <div class="min-w-0">
                            <span class="block text-[10px] font-bold uppercase tracking-wider ${active ? 'text-white/75' : 'text-copper'}">${escapeHtml(item.group)}</span>
                            <span class="block font-semibold truncate">${escapeHtml(item.title)}</span>
                        </div>
                        <span class="text-xs shrink-0 ${active ? 'text-white/85 font-semibold' : 'text-taupe'}">${escapeHtml(item.hint)}</span>
                    </a>
                `;
            }).join('');
        }

        function openPalette() {
            backdrop.classList.remove('hidden');
            input.value = '';
            activeIndex = 0;
            renderList();
            requestAnimationFrame(() => input.focus());
        }

        function closePalette() {
            backdrop.classList.add('hidden');
        }

        window.openKnPalette = openPalette;

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (backdrop.classList.contains('hidden')) {
                    openPalette();
                } else {
                    closePalette();
                }
                return;
            }

            if (backdrop.classList.contains('hidden')) return;

            if (e.key === 'Escape') {
                e.preventDefault();
                closePalette();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (visibleItems.length) {
                    activeIndex = (activeIndex + 1) % visibleItems.length;
                    renderList();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (visibleItems.length) {
                    activeIndex = (activeIndex - 1 + visibleItems.length) % visibleItems.length;
                    renderList();
                }
            } else if (e.key === 'Enter') {
                const target = visibleItems[activeIndex];
                if (target) {
                    e.preventDefault();
                    window.location.href = target.url;
                }
            }
        });

        input.addEventListener('input', () => {
            activeIndex = 0;
            renderList();
        });

        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) closePalette();
        });

        closeBtn?.addEventListener('click', closePalette);
    })();
</script>
