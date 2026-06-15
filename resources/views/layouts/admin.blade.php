<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PharmVR Admin') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        .font-display { font-family: 'Orbitron', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #2A3545; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #00E5FF; }

        /* BULLETPROOF RESPONSIVE UTILITIES (Workaround for Tailwind build issues) */
        @media (max-width: 767px) {
            .side-panel-desktop { display: none !important; }
            .mobile-trigger { display: flex !important; }
            /* Mobile sidebar visibility is managed by Alpine.js x-show */
        }
        @media (min-width: 768px) {
            .side-panel-desktop { display: flex !important; }
            .mobile-trigger { display: none !important; }
            .mobile-sidebar-only { display: none !important; }
        }

        /* High priority z-index for mobile navigation to prevent overlap issues */
        .z-mobile-overlay { z-index: 9998 !important; }
        .z-mobile-sidebar { z-index: 9999 !important; }
    </style>
</head>
<body class="font-sans antialiased bg-background text-text-primary overflow-hidden" 
    x-data="{ 
        sidebarOpen: false,
        toggleSidebar() { this.sidebarOpen = !this.sidebarOpen }
    }"
    @keydown.escape.window="sidebarOpen = false">
    <!-- MOBILE SIDEBAR OVERLAY -->
    <div 
        x-show="sidebarOpen" 
        x-cloak
        @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-mobile-overlay bg-background/80 backdrop-blur-md mobile-sidebar-only"
    ></div>

    <aside 
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition-transform ease-in-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in-out duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-mobile-sidebar w-72 bg-surface text-white flex flex-col border-r border-divider mobile-sidebar-only shadow-[0_0_50px_rgba(0,0,0,0.5)]"
    >
        @include('layouts.admin_sidebar_content')
    </aside>

    <div class="flex h-screen w-full overflow-hidden bg-background">
        <!-- DESKTOP SIDEBAR -->
        <aside class="side-panel-desktop w-72 bg-surface text-white flex-shrink-0 border-r border-divider flex-col relative z-20">
            @include('layouts.admin_sidebar_content')
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 bg-background relative z-10">
            <!-- Header -->
            <header class="h-20 bg-surface/80 backdrop-blur-xl border-b border-divider flex items-center justify-between px-6 md:px-10 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Toggle -->
                    <button @click="toggleSidebar()" class="mobile-trigger p-2 text-text-tertiary hover:text-primary transition-all bg-surface-light/30 rounded-xl border border-divider">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <div>
                        <h2 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] mb-1 opacity-50 italic">System Node /</h2>
                        <h2 class="text-xl font-black text-white tracking-tight">@yield('header', 'Overview')</h2>
                    </div>
                </div>
                
                <div class="flex items-center gap-6">
                    <button class="p-2.5 text-text-tertiary hover:text-primary transition-all bg-surface-light/30 rounded-xl border border-divider">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    
                    <div class="h-8 w-px bg-divider"></div>

                    @auth
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline" onsubmit="return confirm('Terminate the current admin session?');">
                        @csrf
                        <button type="submit" class="text-xs font-black text-white uppercase tracking-[0.2em] bg-red-500/10 hover:bg-red-500 hover:text-white border border-red-500/20 px-5 py-2.5 rounded-xl transition-all duration-300">
                            Terminate
                        </button>
                    </form>
                    @endauth
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 md:p-10 relative">
                <!-- Decorative background elements -->
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[150px] pointer-events-none"></div>

                @if (session('success'))
                    <div class="mb-8 p-5 bg-primary/10 border border-primary/20 text-primary rounded-2xl flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                        <div class="w-8 h-8 rounded-full bg-primary text-background flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-sm font-bold uppercase tracking-widest">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-8 p-5 bg-red-500/10 border border-red-500/20 text-red-400 rounded-2xl flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                        <div class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-sm font-bold uppercase tracking-widest">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="relative z-10">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
