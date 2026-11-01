<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Ruang Kerja Staf') — {{ config('restaurant.name', 'Kafe Nusantara') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            serif: ['Playfair Display', 'Georgia', 'serif'],
                        },
                        colors: {
                            alabaster: '#FAF7F2',
                            sand: '#E8E1D5',
                            'sand-soft': '#F3EDE3',
                            teak: '#1F1A17',
                            'teak-soft': '#2B2420',
                            'teak-card': '#28211D',
                            copper: '#B85D3B',
                            'copper-dark': '#9E4B2D',
                            'copper-tint': '#FDF3EE',
                            brass: '#C69C6D',
                            'brass-tint': '#FAF4EB',
                            taupe: '#6E655F',
                            sage: '#2E6F4E',
                            'sage-tint': '#EAF4EF',
                            saffron: '#B7791F',
                            'saffron-tint': '#FEF6E7',
                            clay: '#B83A3A',
                            'clay-tint': '#FCECEC',
                        }
                    }
                }
            };
        </script>
        <style>
            @view-transition { navigation: auto; }
            body { background-color: #FAF7F2; color: #1F1A17; font-family: 'Plus Jakarta Sans', sans-serif; }
            .tabular-nums { font-variant-numeric: tabular-nums; }
            @keyframes knFadeUp { from { opacity: 0; transform: translate3d(0, 10px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
            @keyframes knSlideDown { from { opacity: 0; transform: translate3d(0, -8px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
            @keyframes knGrowBar { from { opacity: 0.3; transform: scaleY(0.08); } to { opacity: 1; transform: scaleY(1); } }
            @keyframes knPulseSoft { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.45); opacity: 0.55; } }
            @keyframes knSpin { to { transform: rotate(360deg); } }
            .kn-enter { animation: knFadeUp 340ms cubic-bezier(0.22, 1, 0.36, 1) both; }
            .kn-toast { animation: knSlideDown 280ms cubic-bezier(0.22, 1, 0.36, 1) both; transition: opacity 220ms ease, transform 220ms ease; }
            .kn-stagger > * { animation: knFadeUp 360ms cubic-bezier(0.22, 1, 0.36, 1) both; }
            .kn-card-lift { transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 220ms ease, border-color 180ms ease; }
            .kn-card-lift:hover { transform: translate3d(0, -2px, 0); box-shadow: 0 12px 28px -14px rgba(31, 26, 23, 0.16); }
            .kn-bar-grow { transform-origin: bottom; animation: knGrowBar 560ms cubic-bezier(0.22, 1, 0.36, 1) both; }
            .kn-pulse-dot { animation: knPulseSoft 2s ease-in-out infinite; }
            .kn-spinner { animation: knSpin 680ms linear infinite; }
            @media print { .no-print { display: none !important; } body { background: #fff !important; } }
        </style>
    @endif
</head>
<body class="min-h-full bg-alabaster text-teak antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row">
        {{-- Modular Sidebar Navigation --}}
        @include('partials.sidebar')

        {{-- Main Workspace Column --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Modular Topbar --}}
            @include('partials.topbar')

            {{-- Flash Notifications & Validation Summary --}}
            <div class="px-6 lg:px-10 pt-5 no-print">
                @include('partials.flash')
            </div>

            {{-- Main Page Content --}}
            <main data-vt="main" class="kn-enter flex-1 px-6 lg:px-10 py-6 lg:py-8">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.motion')
    @stack('scripts')
</body>
</html>
