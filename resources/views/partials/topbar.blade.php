<header data-vt="topbar" class="no-print bg-white border-b border-sand px-6 lg:px-8 py-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            @hasSection('breadcrumb')
                <div class="text-[11.5px] text-taupe mb-0.5">
                    @yield('breadcrumb')
                </div>
            @endif
            <h1 class="text-xl lg:text-2xl font-bold tracking-tight text-teak">
                @yield('page_title', 'Beranda')
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Practical Global Search --}}
            <form action="{{ route('workspace.search') }}" method="GET" class="relative">
                <svg class="w-4 h-4 text-taupe/70 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text"
                       name="q"
                       value="{{ request()->routeIs('workspace.search') ? request('q') : '' }}"
                       placeholder="Cari menu, order, pelanggan..."
                       class="w-full sm:w-60 pl-9 pr-3.5 py-2 rounded-lg bg-alabaster border border-sand text-xs font-medium text-teak placeholder:text-taupe/65 focus:outline-none focus:bg-white focus:border-copper transition">
            </form>

            {{-- Primary Page Action Button(s) --}}
            @yield('header_actions')
        </div>
    </div>
</header>
