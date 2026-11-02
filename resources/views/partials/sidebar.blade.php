@php
    $operationalNav = [
        [
            'label' => 'Ringkasan Gerai',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard') || request()->routeIs('workspace.search'),
            'icon' => 'dashboard',
        ],
        [
            'label' => 'Pesanan & Kasir',
            'route' => 'orders.index',
            'active' => request()->routeIs('orders.*'),
            'icon' => 'orders',
        ],
        [
            'label' => 'Layar Dapur',
            'route' => 'kitchen.index',
            'active' => request()->routeIs('kitchen.*'),
            'icon' => 'kitchen',
            'badge' => $kitchenQueueCount ?? 0,
        ],
        [
            'label' => 'Status Meja',
            'route' => 'tables.index',
            'active' => request()->routeIs('tables.*'),
            'icon' => 'tables',
        ],
    ];

    $catalogNav = [
        [
            'label' => 'Kategori Menu',
            'route' => 'categories.index',
            'active' => request()->routeIs('categories.*'),
            'icon' => 'categories',
        ],
        [
            'label' => 'Daftar Menu',
            'route' => 'menus.index',
            'active' => request()->routeIs('menus.*'),
            'icon' => 'menus',
        ],
    ];
@endphp

<aside data-vt="sidebar" class="no-print w-full lg:w-60 lg:min-h-screen kn-batik-sidebar text-alabaster flex flex-col justify-between shrink-0 lg:sticky lg:top-0 lg:h-screen select-none border-b lg:border-b-0 lg:border-r border-brass/25">
    {{-- Brand Lockup & Grouped POS Navigation --}}
    <div>
        <div class="px-5 pt-4 pb-3.5 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <span class="w-9 h-9 rounded-lg bg-teak-soft border border-brass/55 text-brass font-serif font-bold text-sm flex items-center justify-center shrink-0 shadow-xs group-hover:border-brass transition">
                    KN
                </span>
                <div>
                    <span class="block font-serif text-[16px] font-bold tracking-tight text-white leading-tight">
                        Kafe Nusantara
                    </span>
                    <span class="block text-[9.5px] font-semibold uppercase tracking-[0.18em] text-brass/80 mt-0.5">
                        POS & Katalog
                    </span>
                </div>
            </a>

            {{-- Mobile Menu Toggle --}}
            <button type="button" onclick="document.getElementById('mobile-nav-panel').classList.toggle('hidden')" class="lg:hidden p-2 rounded-md text-alabaster/70 hover:text-white hover:bg-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        {{-- Gold Sulur / Floral Filigree Divider --}}
        <div class="px-5 flex items-center gap-2 opacity-75" aria-hidden="true">
            <span class="h-px flex-1 bg-gradient-to-r from-transparent via-brass/55 to-brass/55"></span>
            <svg class="w-3.5 h-3.5 text-brass shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C10.5 6 8 8.5 4 10c4 1.5 6.5 4 8 8 1.5-4 4-6.5 8-8-4-1.5-6.5-4-8-8Zm0 7.2a2.8 2.8 0 1 1 0 5.6 2.8 2.8 0 0 1 0-5.6Z"/>
            </svg>
            <span class="h-px flex-1 bg-gradient-to-l from-transparent via-brass/55 to-brass/55"></span>
        </div>

        <div id="mobile-nav-panel" class="hidden lg:block px-3 py-4 space-y-5">
            {{-- Group 1: Operasional --}}
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-widest text-alabaster/40">
                    Operasional
                </p>
                <nav class="space-y-0.5">
                    @foreach ($operationalNav as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center justify-between gap-2 px-2.5 py-2 rounded-md text-[13px] transition {{ $item['active'] ? 'bg-[#29221D]/95 text-white font-semibold border-l-2 border-brass pl-[9px] shadow-xs' : 'text-alabaster/75 hover:text-white hover:bg-white/[0.06] font-medium' }}">
                            <span class="flex items-center gap-2.5 min-w-0">
                                @switch($item['icon'])
                                    @case('dashboard')
                                        <svg class="w-4 h-4 shrink-0 {{ $item['active'] ? 'text-brass' : 'text-alabaster/50' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
                                        </svg>
                                        @break
                                    @case('orders')
                                        <svg class="w-4 h-4 shrink-0 {{ $item['active'] ? 'text-brass' : 'text-alabaster/50' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/>
                                        </svg>
                                        @break
                                    @case('kitchen')
                                        <svg class="w-4 h-4 shrink-0 {{ $item['active'] ? 'text-brass' : 'text-alabaster/50' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" x2="18" y1="17" y2="17"/>
                                        </svg>
                                        @break
                                    @case('tables')
                                        <svg class="w-4 h-4 shrink-0 {{ $item['active'] ? 'text-brass' : 'text-alabaster/50' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/><path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v2H6v-2a2 2 0 0 0-4 0Z"/><path d="M4 18v2"/><path d="M20 18v2"/>
                                        </svg>
                                        @break
                                @endswitch
                                <span class="truncate">{{ $item['label'] }}</span>
                            </span>

                            @if (! empty($item['badge']))
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold tabular-nums bg-copper text-white">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Group 2: Master Katalog --}}
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-widest text-alabaster/40">
                    Master Katalog
                </p>
                <nav class="space-y-0.5">
                    @foreach ($catalogNav as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] transition {{ $item['active'] ? 'bg-[#29221D]/95 text-white font-semibold border-l-2 border-brass pl-[9px] shadow-xs' : 'text-alabaster/75 hover:text-white hover:bg-white/[0.06] font-medium' }}">
                            @if ($item['icon'] === 'categories')
                                <svg class="w-4 h-4 shrink-0 {{ $item['active'] ? 'text-brass' : 'text-alabaster/50' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18M3 12h18M3 18h12"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 shrink-0 {{ $item['active'] ? 'text-brass' : 'text-alabaster/50' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                </svg>
                            @endif
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Group 3: Katalog Pelanggan --}}
            <div class="pt-3 border-t border-white/10">
                <a href="{{ route('menu-book') }}"
                   class="flex items-center justify-between px-2.5 py-2 rounded-md text-xs font-medium text-alabaster/75 hover:text-white hover:bg-white/5 transition">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-brass shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/>
                            <path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/>
                        </svg>
                        <span>Buku Menu Tamu</span>
                    </span>
                    <span class="text-alabaster/40 text-[11px]">↗</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Practical Outlet Info + Active Staff Shift Switcher --}}
    <div class="hidden lg:block px-3 pb-4 space-y-3 border-t border-brass/20 pt-3 bg-[#161311]/70 backdrop-blur-[2px]">
        <div class="px-2.5 py-2 rounded-md bg-white/[0.04] border border-brass/15 text-[11px] text-alabaster/60 space-y-0.5 tabular-nums">
            <div class="flex items-center justify-between text-alabaster/80 font-semibold">
                <span>Cabang Utama</span>
                <span class="text-emerald-400 text-[10px]">● Aktif</span>
            </div>
            <p>PB1 {{ (int) (config('restaurant.tax_rate', 0.10) * 100) }}% · Layanan {{ (int) (config('restaurant.service_charge_rate', 0.05) * 100) }}%</p>
        </div>

        @if ($activeStaff)
            <div class="relative">
                <button type="button"
                        id="staff-switcher-trigger"
                        onclick="document.getElementById('staff-switcher-menu').classList.toggle('hidden')"
                        class="w-full flex items-center justify-between gap-2 px-2 py-1.5 rounded-md hover:bg-white/5 transition text-left cursor-pointer">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-md bg-teak-soft border border-white/15 text-brass font-bold text-xs flex items-center justify-center shrink-0">
                            {{ $activeStaff->initials }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-white truncate">{{ $activeStaff->name }}</p>
                            <p class="text-[11px] text-alabaster/50">{{ $activeStaff->role_label }}</p>
                        </div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-alabaster/45 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7 15 5 5 5-5M7 9l5-5 5 5"/>
                    </svg>
                </button>

                <div id="staff-switcher-menu" class="hidden absolute bottom-12 left-0 right-0 rounded-lg bg-teak-soft border border-white/15 shadow-xl p-1.5 z-50">
                    <p class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-alabaster/45">
                        Petugas Shift
                    </p>
                    <div class="max-h-52 overflow-y-auto space-y-0.5">
                        @foreach ($staffList as $staff)
                            <form action="{{ route('staff.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                <button type="submit"
                                        class="w-full flex items-center justify-between px-2 py-1.5 rounded text-left text-xs transition {{ $staff->id === $activeStaff->id ? 'bg-copper text-white font-semibold' : 'text-alabaster/80 hover:bg-white/10' }}">
                                    <span class="truncate">{{ $staff->name }}</span>
                                    <span class="text-[10.5px] opacity-75 ml-2 shrink-0">{{ $staff->role_label }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</aside>
