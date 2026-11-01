<header data-vt="topbar" class="no-print bg-white border-b border-sand px-6 lg:px-10 py-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            @hasSection('breadcrumb')
                <div class="text-xs text-taupe mb-1">
                    @yield('breadcrumb')
                </div>
            @endif
            <h1 class="text-2xl lg:text-[26px] font-bold tracking-tight text-teak">
                @yield('page_title', 'Ruang Kerja')
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Global Workspace Search --}}
            <form action="{{ route('workspace.search') }}" method="GET" class="relative">
                <svg class="w-4 h-4 text-taupe/70 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text"
                       name="q"
                       value="{{ request()->routeIs('workspace.search') ? request('q') : '' }}"
                       placeholder="Cari di ruang kerja..."
                       class="w-full sm:w-60 pl-9 pr-4 py-2.5 rounded-xl bg-alabaster border border-sand text-sm text-teak placeholder:text-taupe/65 focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15 transition">
            </form>

            {{-- Primary Page Action Button(s) --}}
            @yield('header_actions')
        </div>
    </div>
</header>
