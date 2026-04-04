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
        .font-display { font-family: 'Orbitron', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #2A3545; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #00E5FF; }
    </style>
</head>
<body class="font-sans antialiased bg-background text-text-primary">
    <div class="min-h-screen flex flex-col md:flex-row h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-full md:w-72 bg-surface text-white flex-shrink-0 border-r border-divider flex flex-col relative z-20">
            <div class="p-8 pb-4">
                <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-start gap-1 transition-transform hover:scale-[1.02] group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16 w-auto object-contain drop-shadow-[0_0_12px_rgba(0,229,255,0.25)] group-hover:drop-shadow-[0_0_15px_rgba(0,229,255,0.4)] transition-all">
                    <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em] opacity-40 ml-1 italic">Command Center</span>
                </a>
            </div>
            
            <nav class="flex-1 overflow-y-auto px-6 py-4 space-y-8">
                <div>
                    <p class="px-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-4 opacity-50">Command</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            <span class="text-sm">Dashboard</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-4 opacity-50">Management</p>
                    <div class="space-y-1">
                        @can('manage-users')
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.users.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="text-sm">Human Assets</span>
                        </a>
                        @endcan
                        
                        @can('manage-content')
                        <a href="{{ route('admin.education.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.education.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.education.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span class="text-sm">Modules</span>
                        </a>

                        <a href="{{ route('admin.videos.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.videos.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.videos.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span class="text-sm">Educational Videos</span>
                        </a>

                        <a href="{{ route('admin.documents.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.documents.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.documents.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm">Educational Documents</span>
                        </a>

                        <a href="{{ route('admin.assessments.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.assessments.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.assessments.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span class="text-sm">Assessments</span>
                        </a>
                        
                        <a href="{{ route('admin.news.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.news.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.news.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm">Global News</span>
                        </a>

                        <a href="{{ route('admin.news-sources.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.news-sources.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.news-sources.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-sm">External Sources</span>
                        </a>
                        @endcan
                    </div>
                </div>

                <div>
                    <p class="px-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-4 opacity-50">Oversight</p>
                    <div class="space-y-1">
                        @can('view-monitoring')
                        <a href="{{ route('admin.monitoring.vr') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.monitoring.vr*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.monitoring.vr*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.75 17L9 21h6l-.75-4M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="text-sm">VR Live Feed</span>
                        </a>
                        
                        <a href="{{ route('admin.monitoring.progress') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.monitoring.progress*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.monitoring.progress*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="text-sm">Student Progress</span>
                        </a>

                        <a href="{{ route('admin.reporting.assessments') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.reporting.assessments*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.reporting.assessments*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm">Assessment Report</span>
                        </a>

                        <a href="{{ route('admin.monitoring.ai') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.monitoring.ai*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.monitoring.ai*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span class="text-sm">Neural Analytics</span>
                        </a>
                        @endcan
                    </div>
                </div>

                <div>
                    <p class="px-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-4 opacity-50">Analytics Hub</p>
                    <div class="space-y-1">
                        @can('view-monitoring')
                        <a href="{{ route('admin.advanced-reports.hub') }}" class="flex flex-col gap-1 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.advanced-reports.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.advanced-reports.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span class="text-sm">Advanced Reports</span>
                            </div>
                            <span class="text-[9px] pl-9 opacity-50 font-bold uppercase tracking-wider group-hover:opacity-80">Data Analytics Suite</span>
                        </a>
                        @endcan
                    </div>
                </div>

                <div>
                    <p class="px-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-4 opacity-50">Neural Link</p>
                    <div class="space-y-1">
                        <a href="{{ route('admin.ai.dashboard') }}" class="flex flex-col gap-1 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.ai.dashboard') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.ai.dashboard') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span class="text-sm">Neural Dashboard</span>
                            </div>
                            <span class="text-[9px] pl-9 opacity-50 font-bold uppercase tracking-wider group-hover:opacity-80">AI Operations Overview</span>
                        </a>
                        
                        <a href="{{ route('admin.ai.sources.index') }}" class="flex flex-col gap-1 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.ai.sources.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.ai.sources.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <span class="text-sm">Knowledge Matrix</span>
                            </div>
                            <span class="text-[9px] pl-9 opacity-50 font-bold uppercase tracking-wider group-hover:opacity-80">Trusted Knowledge Sources</span>
                        </a>

                        <a href="{{ route('admin.ai.avatars.index') }}" class="flex flex-col gap-1 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.ai.avatars.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.ai.avatars.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span class="text-sm">Avatar Network</span>
                            </div>
                            <span class="text-[9px] pl-9 opacity-50 font-bold uppercase tracking-wider group-hover:opacity-80">VR Guide Profiles</span>
                        </a>

                        <a href="{{ route('admin.ai.scene-prompts.index') }}" class="flex flex-col gap-1 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.ai.scene-prompts.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.ai.scene-prompts.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span class="text-sm">Scene Directives</span>
                            </div>
                            <span class="text-[9px] pl-9 opacity-50 font-bold uppercase tracking-wider group-hover:opacity-80">Scene-Based Prompt Rules</span>
                        </a>

                        <a href="{{ route('admin.ai.logs.index') }}" class="flex flex-col gap-1 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.ai.logs.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.ai.logs.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-sm">Neural Logs</span>
                            </div>
                            <span class="text-[9px] pl-9 opacity-50 font-bold uppercase tracking-wider group-hover:opacity-80">AI Interaction Logs</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-4 opacity-50">Hardware Ops</p>
                    <div class="space-y-1">
                        @can('view-monitoring')
                        <a href="{{ route('admin.vr-devices.index') }}" class="flex flex-col gap-1 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.vr-devices.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <svg class="w-5 h-5 {{ request()->routeIs('admin.vr-devices.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <span class="text-sm">VR Fleet Grid</span>
                            </div>
                            <span class="text-[9px] pl-9 opacity-50 font-bold uppercase tracking-wider group-hover:opacity-80">Device Status Matrix</span>
                        </a>
                        @endcan
                    </div>
                </div>

                <div>
                    <p class="px-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-4 opacity-50">Governance</p>
                    <div class="space-y-1">
                        @can('manage-system')
                        <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.audit-logs.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.audit-logs.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="text-sm">Audit Trails</span>
                        </a>

                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.settings.*') ? 'bg-primary text-background font-black' : 'text-text-secondary hover:bg-surface-light hover:text-white' }} transition-all duration-300 group">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.settings.*') ? 'text-background' : 'text-primary/70 group-hover:text-primary' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="text-sm">Platform Settings</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </nav>
            
            <div class="p-6 border-t border-divider">
                <div class="flex items-center gap-4 p-4 bg-surface-light/50 rounded-2xl border border-divider">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-sm font-black text-background">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate text-white uppercase tracking-tight">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[9px] text-primary font-black uppercase tracking-[0.2em] opacity-80 italic">Root Access</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden bg-background">
            <!-- Header -->
            <header class="h-20 bg-surface/80 backdrop-blur-xl border-b border-divider flex items-center justify-between px-10 z-10 flex-shrink-0">
                <div>
                    <h2 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] mb-1 opacity-50 italic">System Node /</h2>
                    <h2 class="text-xl font-black text-white tracking-tight">@yield('header', 'Overview')</h2>
                </div>
                
                <div class="flex items-center gap-6">
                    <button class="p-2.5 text-text-tertiary hover:text-primary transition-all bg-surface-light/30 rounded-xl border border-divider">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    
                    <div class="h-8 w-px bg-divider"></div>

                    <div x-data="{ showLogoutModal: false }" class="inline">
                        <button @click="showLogoutModal = true" type="button" class="text-xs font-black text-white uppercase tracking-[0.2em] bg-red-500/10 hover:bg-red-500 hover:text-white border border-red-500/20 px-5 py-2.5 rounded-xl transition-all duration-300">
                            Terminate
                        </button>

                        <!-- Logout Confirmation Modal -->
                        <template x-teleport="body">
                            <div x-show="showLogoutModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <!-- Background overlay -->
                                    <div x-show="showLogoutModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-background/90 backdrop-blur-sm" aria-hidden="true" @click="showLogoutModal = false"></div>

                                    <!-- Center modal vertically -->
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                    <!-- Modal panel -->
                                    <div x-show="showLogoutModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-surface border border-red-500/30 rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 relative">
                                        
                                        <!-- Decorative elements -->
                                        <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/10 rounded-full blur-[50px] pointer-events-none"></div>

                                        <div class="sm:flex sm:items-start relative z-10">
                                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-500/10 rounded-full sm:mx-0 sm:h-10 sm:w-10 border border-red-500/30">
                                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg font-black leading-6 text-white tracking-widest uppercase font-display" id="modal-title">
                                                    Terminate Session
                                                </h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-text-secondary">
                                                        Are you sure you want to terminate the current session and disconnect from the Command Center? Any unsaved changes may be lost.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-8 sm:mt-6 sm:flex sm:flex-row-reverse relative z-10 gap-3">
                                            <form action="{{ route('admin.logout') }}" method="POST" class="w-full sm:w-auto">
                                                @csrf
                                                <button type="submit" class="inline-flex justify-center w-full px-5 py-2.5 text-xs font-black text-white uppercase tracking-[0.2em] bg-red-600 border border-transparent rounded-xl shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 focus:ring-offset-surface transition-all duration-300">
                                                    Confirm Disconnect
                                                </button>
                                            </form>
                                            <button @click="showLogoutModal = false" type="button" class="mt-3 sm:mt-0 inline-flex justify-center w-full px-5 py-2.5 text-xs font-black text-white uppercase tracking-[0.2em] bg-surface-light border border-divider rounded-xl shadow-sm hover:bg-surface hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary focus:ring-offset-surface transition-all duration-300">
                                                Abort
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto p-10 relative">
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
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
