@php
    $navItems = [
        [
            'label' => 'Ringkasan Gerai',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard') || request()->routeIs('workspace.search'),
            'icon' => 'dashboard',
        ],
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
        [
            'label' => 'Pesanan & Kasir',
            'route' => 'orders.index',
            'active' => request()->routeIs('orders.*'),
            'icon' => 'orders',
        ],
        [
            'label' => 'Status Meja',
            'route' => 'tables.index',
            'active' => request()->routeIs('tables.*'),
            'icon' => 'tables',
        ],
    ];
@endphp

<aside data-vt="sidebar" class="no-print w-full lg:w-64 lg:min-h-screen bg-teak text-alabaster flex flex-col justify-between shrink-0 lg:sticky lg:top-0 lg:h-screen select-none border-b lg:border-b-0 lg:border-r border-white/10">
    {{-- Brand & Navigation --}}
    <div>
        <div class="px-6 pt-7 pb-6 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 group">
                <span class="w-10 h-10 rounded-full border border-brass/50 bg-teak-soft flex items-center justify-center text-brass group-hover:border-brass transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 8h1a4 4 0 1 1 0 8h-1"/>
                        <path d="M3 8h14v7a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
                        <line x1="6" x2="6" y1="2" y2="4"/>
                        <line x1="10" x2="10" y1="2" y2="4"/>
                        <line x1="14" x2="14" y1="2" y2="4"/>
                    </svg>
                </span>
                <span class="font-serif text-[21px] leading-[1.15] tracking-tight text-alabaster">
                    Kafe<br>Nusantara
                </span>
            </a>

            {{-- Mobile Menu Toggle --}}
            <button type="button" onclick="document.getElementById('mobile-nav-panel').classList.toggle('hidden')" class="lg:hidden p-2 rounded-lg text-alabaster/70 hover:text-white hover:bg-white/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div id="mobile-nav-panel" class="hidden lg:block px-3 pb-4">
            <p class="px-3 mb-2.5 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-alabaster/45">
                Ruang Kerja Staf
            </p>

            <nav class="space-y-1">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition {{ $item['active'] ? 'bg-teak-soft text-white font-semibold shadow-sm' : 'text-alabaster/70 hover:text-white hover:bg-white/5 font-medium' }}">
                        @if ($item['active'])
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 rounded-r-full bg-brass"></span>
                        @endif

                        @switch($item['icon'])
                            @case('dashboard')
                                <svg class="w-[18px] h-[18px] {{ $item['active'] ? 'text-brass' : 'text-alabaster/55' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
                                </svg>
                                @break
                            @case('categories')
                                <svg class="w-[18px] h-[18px] {{ $item['active'] ? 'text-brass' : 'text-alabaster/55' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="7" height="7" x="3" y="3" rx="1.5"/><rect width="7" height="7" x="14" y="3" rx="1.5"/>
                                    <rect width="7" height="7" x="14" y="14" rx="1.5"/><rect width="7" height="7" x="3" y="14" rx="1.5"/>
                                </svg>
                                @break
                            @case('menus')
                                <svg class="w-[18px] h-[18px] {{ $item['active'] ? 'text-brass' : 'text-alabaster/55' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                </svg>
                                @break
                            @case('orders')
                                <svg class="w-[18px] h-[18px] {{ $item['active'] ? 'text-brass' : 'text-alabaster/55' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/>
                                    <path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/>
                                </svg>
                                @break
                            @case('tables')
                                <svg class="w-[18px] h-[18px] {{ $item['active'] ? 'text-brass' : 'text-alabaster/55' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v3"/>
                                    <path d="M2 11v5a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-5a2 2 0 0 0-4 0v2H6v-2a2 2 0 0 0-4 0Z"/>
                                    <path d="M4 18v2"/><path d="M20 18v2"/>
                                </svg>
                                @break
                        @endswitch

                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- Operational Status Card + Staff Shift Switcher --}}
    <div class="hidden lg:block px-4 pb-5 space-y-4">
        <div class="rounded-xl bg-teak-card border border-white/10 p-3.5">
            <div class="flex items-center gap-2 text-xs font-semibold text-alabaster/90 mb-1">
                <svg class="w-4 h-4 text-brass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                    <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/>
                    <path d="M2 7h20"/>
                </svg>
                <span>Operasional gerai</span>
            </div>
            <p class="text-[11px] text-alabaster/55">Data per {{ now()->translatedFormat('d F Y') }}</p>
            <p class="text-[11px] text-alabaster/45 mt-1">Cita rasa lokal. Layanan sepenuh hati.</p>
        </div>

        @if ($activeStaff)
            <div class="relative">
                <button type="button"
                        id="staff-switcher-trigger"
                        onclick="document.getElementById('staff-switcher-menu').classList.toggle('hidden')"
                        class="w-full flex items-center justify-between gap-2.5 px-2 py-2 rounded-xl hover:bg-white/5 transition text-left cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-full bg-brass text-teak font-bold text-xs flex items-center justify-center shrink-0">
                            {{ $activeStaff->initials }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ $activeStaff->name }}</p>
                            <p class="text-xs text-alabaster/55">{{ $activeStaff->role_label }}</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-alabaster/50 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7 15 5 5 5-5M7 9l5-5 5 5"/>
                    </svg>
                </button>

                {{-- 1-Click Staff Shift Switcher Popover --}}
                <div id="staff-switcher-menu" class="hidden absolute bottom-14 left-0 right-0 rounded-xl bg-teak-soft border border-white/15 shadow-2xl p-2 z-50">
                    <p class="px-2.5 py-1.5 text-[10.5px] font-semibold uppercase tracking-wider text-alabaster/45">
                        Ganti Staf Shift Aktif
                    </p>
                    <div class="max-h-56 overflow-y-auto space-y-0.5">
                        @foreach ($staffList as $staff)
                            <form action="{{ route('staff.switch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                <button type="submit"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left text-xs transition {{ $staff->id === $activeStaff->id ? 'bg-copper text-white font-semibold' : 'text-alabaster/80 hover:bg-white/10' }}">
                                    <span class="truncate">{{ $staff->name }}</span>
                                    <span class="text-[11px] opacity-75 ml-2 shrink-0">{{ $staff->role_label }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</aside>
