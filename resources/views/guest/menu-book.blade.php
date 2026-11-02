<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Menu Digital — {{ config('restaurant.name', 'Kafe Nusantara') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-alabaster text-teak antialiased pb-28">
    {{-- Floral Batik Coffeehouse Header --}}
    <header class="kn-batik-banner text-alabaster border-b border-brass/30">
        <div class="max-w-5xl mx-auto px-5 sm:px-8 py-7">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <span class="w-11 h-11 rounded-lg bg-teak-soft border border-brass/60 text-brass font-serif font-bold text-base flex items-center justify-center shrink-0 shadow-xs">
                        KN
                    </span>
                    <div>
                        <p class="text-[10.5px] uppercase tracking-[0.18em] text-brass font-bold">Buku Sajian Resmi</p>
                        <h1 class="font-serif text-xl sm:text-2xl font-bold text-white tracking-tight">Kafe Nusantara</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    @if ($selectedTable)
                        <span class="px-3 py-1.5 rounded-md bg-white/10 border border-brass/40 text-xs font-bold text-brass">
                            Meja {{ $selectedTable->number }} · {{ $selectedTable->area }}
                        </span>
                    @endif
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-1.5 rounded-md bg-white/10 hover:bg-white/15 text-xs font-semibold text-alabaster transition">
                        ← Panel Staf
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Sticky Category Navigation + Search Bar --}}
    <div class="sticky top-0 z-30 bg-alabaster/92 backdrop-blur-md border-b border-sand shadow-2xs">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 py-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                <a href="#unggulan"
                   class="px-3.5 py-1.5 rounded-full bg-copper text-white text-xs font-bold shrink-0">
                    ★ Unggulan
                </a>
                @foreach ($categories as $category)
                    <a href="#kat-{{ $category->slug }}"
                       class="px-3.5 py-1.5 rounded-full bg-white border border-sand hover:border-copper text-xs font-semibold text-teak shrink-0 transition">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <div class="relative w-full md:w-64 shrink-0">
                <svg class="w-4 h-4 text-taupe/70 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text"
                       id="guest-menu-search"
                       placeholder="Cari sajian favorit..."
                       class="w-full pl-9 pr-4 py-2 rounded-full bg-white border border-sand text-xs text-teak placeholder:text-taupe/65 focus:outline-none focus:border-copper">
            </div>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-5 sm:px-8 py-10 space-y-12">
        {{-- Chef's Signature / Best Seller Showcase --}}
        @if ($featuredMenus->isNotEmpty())
            <section id="unggulan" class="guest-section">
                <div class="flex items-end justify-between gap-4 mb-5">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-copper">Rekomendasi Barista & Dapur</span>
                        <h2 class="font-serif text-2xl sm:text-3xl font-bold text-teak mt-0.5">Sajian Paling Dicintai</h2>
                    </div>
                </div>

                <div class="kn-stagger grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($featuredMenus as $menu)
                        <div class="guest-card group kn-card-lift bg-white rounded-2xl border border-brass/45 p-4 flex flex-col justify-between shadow-2xs"
                             data-search="{{ mb_strtolower($menu->name.' '.$menu->description.' '.($menu->category?->name ?? '')) }}">
                            <div>
                                <div class="relative h-44 rounded-xl bg-sand-soft overflow-hidden mb-4">
                                    <div class="absolute top-3 left-3 z-10">
                                        <x-status-badge tone="brass" label="★ Best Seller" />
                                    </div>
                                    @if ($menu->image_url)
                                        <img src="{{ $menu->image_url }}"
                                             alt="{{ $menu->name }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    @endif
                                </div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-brass">{{ $menu->category?->name }}</p>
                                <h3 class="font-serif text-lg font-bold text-teak mt-0.5">{{ $menu->name }}</h3>
                                <p class="text-xs text-taupe mt-1.5 leading-relaxed">{{ $menu->description }}</p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-sand/70 flex items-center justify-between">
                                <span class="text-base font-bold text-copper tabular-nums">{{ $menu->formatted_price }}</span>
                                <button type="button"
                                        data-pick-id="{{ $menu->id }}"
                                        data-pick-name="{{ $menu->name }}"
                                        data-pick-price="{{ $menu->price }}"
                                        class="guest-add-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-copper-tint hover:bg-copper text-copper hover:text-white text-xs font-bold transition cursor-pointer">
                                    <span>+ Catat Pilihan</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Categorized Full Menu Sections --}}
        @foreach ($categories as $category)
            <section id="kat-{{ $category->slug }}" class="guest-section scroll-mt-20">
                <div class="flex items-center gap-3.5 pb-4 mb-6 border-b border-sand">
                    <x-category-icon :slug="$category->slug" :icon="$category->icon" size="lg" />
                    <div>
                        <h2 class="font-serif text-2xl font-bold text-teak">{{ $category->name }}</h2>
                        <p class="text-xs sm:text-sm text-taupe mt-0.5">
                            {{ $category->description ?: 'Disiapkan segar dengan bahan baku berkualitas.' }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($category->menus as $menu)
                        <div class="guest-card kn-card-lift bg-white rounded-2xl border border-sand p-4 flex items-center gap-4 {{ $menu->is_available ? '' : 'opacity-65' }}"
                             data-search="{{ mb_strtolower($menu->name.' '.$menu->description.' '.$category->name) }}">
                            <div class="w-24 h-24 rounded-xl overflow-hidden border border-sand bg-sand-soft shrink-0">
                                @if ($menu->image_url)
                                    <img src="{{ $menu->image_url }}"
                                         alt="{{ $menu->name }}"
                                         loading="lazy"
                                         decoding="async"
                                         class="w-full h-full max-w-none object-cover {{ $menu->is_available ? '' : 'grayscale' }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <x-category-icon :slug="$category->slug" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-bold text-teak">{{ $menu->name }}</h3>
                                    @if ($menu->is_featured)
                                        <x-status-badge tone="brass" label="Best Seller" />
                                    @endif
                                </div>
                                <p class="text-xs text-taupe mt-1 line-clamp-2 leading-relaxed">
                                    {{ $menu->description ?: 'Racikan khas Kafe Nusantara.' }}
                                </p>
                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <span class="text-sm font-bold text-copper tabular-nums">{{ $menu->formatted_price }}</span>

                                    @if ($menu->is_available)
                                        <button type="button"
                                                data-pick-id="{{ $menu->id }}"
                                                data-pick-name="{{ $menu->name }}"
                                                data-pick-price="{{ $menu->price }}"
                                                class="guest-add-btn px-3 py-1 rounded-lg border border-sand hover:border-copper text-xs font-bold text-teak hover:text-copper transition cursor-pointer">
                                            + Pilih
                                        </button>
                                    @else
                                        <x-status-badge tone="clay" label="Habis Hari Ini" />
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        {{-- Footer Note --}}
        <footer class="pt-8 border-t border-sand text-center text-xs text-taupe space-y-1">
            <p class="font-semibold text-teak">Harga belum termasuk Biaya Layanan Makan di Tempat (5%) dan Pajak Restoran PB1 (10%).</p>
            <p>Silakan sebutkan pilihan sajian Anda beserta nomor meja kepada staf pelayan atau kasir Kafe Nusantara.</p>
        </footer>
    </main>

    {{-- Floating Guest Selection Estimator Bar (Shows when guest taps "+ Pilih") --}}
    <div id="guest-tray" class="hidden fixed bottom-5 inset-x-4 max-w-2xl mx-auto z-40">
        <div class="rounded-2xl bg-teak text-alabaster border border-brass/40 shadow-2xl p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-brass">
                        Catatan Pilihan Saya {{ $selectedTable ? '· Meja '.$selectedTable->number : '' }}
                    </p>
                    <p class="text-sm font-bold text-white tabular-nums mt-0.5" id="guest-tray-summary">0 porsi</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                            id="guest-tray-toggle"
                            class="px-3.5 py-2 rounded-xl bg-brass text-teak text-xs font-bold cursor-pointer">
                        Lihat Rincian
                    </button>
                    <button type="button"
                            id="guest-tray-clear"
                            title="Kosongkan catatan"
                            class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-alabaster/75 text-xs cursor-pointer">
                        ✕
                    </button>
                </div>
            </div>

            <div id="guest-tray-details" class="hidden mt-3.5 pt-3.5 border-t border-white/15 space-y-2 text-xs">
                <div id="guest-tray-items" class="space-y-1.5 max-h-40 overflow-y-auto"></div>
                <div class="pt-2 border-t border-white/10 flex justify-between text-alabaster/75">
                    <span>Estimasi (+ Layanan 5% & PB1 10%)</span>
                    <span class="font-bold text-brass tabular-nums" id="guest-tray-grand">Rp 0</span>
                </div>
                <p class="text-[11px] text-alabaster/55 italic">
                    Tunjukkan layar ini kepada pelayan atau kasir untuk mempercepat pencatatan pesanan Anda.
                </p>
            </div>
        </div>
    </div>

    @include('partials.motion')

    <script>
        (function () {
            const SERVICE = {{ $serviceRate }};
            const TAX = {{ $taxRate }};
            const picks = new Map();

            const tray = document.getElementById('guest-tray');
            const summaryEl = document.getElementById('guest-tray-summary');
            const detailsBox = document.getElementById('guest-tray-details');
            const itemsEl = document.getElementById('guest-tray-items');
            const grandEl = document.getElementById('guest-tray-grand');

            function rp(n) {
                return 'Rp ' + Math.round(n).toLocaleString('id-ID');
            }

            function renderTray() {
                let portions = 0;
                let subtotal = 0;
                const rows = [];

                picks.forEach((item, id) => {
                    portions += item.qty;
                    subtotal += item.qty * item.price;
                    rows.push(`
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate">${item.qty}× ${item.name}</span>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="tabular-nums text-alabaster/85">${rp(item.qty * item.price)}</span>
                                <button type="button" data-dec="${id}" class="w-5 h-5 rounded bg-white/10 text-white font-bold cursor-pointer">−</button>
                            </div>
                        </div>
                    `);
                });

                if (portions === 0) {
                    tray.classList.add('hidden');
                    detailsBox.classList.add('hidden');
                    return;
                }

                const withService = subtotal + Math.round(subtotal * SERVICE);
                const grand = withService + Math.round(withService * TAX);

                tray.classList.remove('hidden');
                summaryEl.textContent = `${portions} porsi dipilih · Subtotal ${rp(subtotal)}`;
                itemsEl.innerHTML = rows.join('');
                grandEl.textContent = rp(grand);

                itemsEl.querySelectorAll('[data-dec]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.dec;
                        const cur = picks.get(id);
                        if (!cur) return;
                        if (cur.qty <= 1) picks.delete(id);
                        else cur.qty -= 1;
                        renderTray();
                    });
                });
            }

            document.querySelectorAll('.guest-add-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.pickId;
                    const existing = picks.get(id);
                    if (existing) {
                        existing.qty += 1;
                    } else {
                        picks.set(id, {
                            name: btn.dataset.pickName,
                            price: parseInt(btn.dataset.pickPrice, 10),
                            qty: 1,
                        });
                    }
                    renderTray();
                });
            });

            document.getElementById('guest-tray-toggle')?.addEventListener('click', () => {
                detailsBox.classList.toggle('hidden');
            });

            document.getElementById('guest-tray-clear')?.addEventListener('click', () => {
                picks.clear();
                renderTray();
            });

            document.getElementById('guest-menu-search')?.addEventListener('input', function (e) {
                const q = (e.target.value || '').trim().toLowerCase();
                document.querySelectorAll('.guest-card').forEach(card => {
                    card.classList.toggle('hidden', q !== '' && !card.dataset.search.includes(q));
                });
            });
        })();
    </script>
</body>
</html>
