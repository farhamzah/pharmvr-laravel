@extends('layouts.admin')

@section('header', 'System Intelligence')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <!-- Total Users -->
    <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-primary/30 transition-all group">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center border border-primary/20 transition-transform group-hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">Registered Assets</span>
                <p class="text-3xl font-black text-white leading-none mt-2 font-display italic tracking-tight">{{ number_format($totalUsers) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-[10px] font-black text-primary bg-primary/5 border border-primary/10 px-3 py-1.5 rounded-full w-fit uppercase tracking-widest">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            Growth Verified
        </div>
    </div>

    <!-- Active VR -->
    <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-primary/30 transition-all group">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-emerald-500/10 text-emerald-400 rounded-2xl flex items-center justify-center border border-emerald-500/20 transition-transform group-hover:scale-110 relative">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.75 17L9 21h6l-.75-4M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span class="absolute top-1 right-1 w-3 h-3 bg-emerald-400 rounded-full border-2 border-surface animate-pulse"></span>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">Active Nodes</span>
                <p class="text-3xl font-black text-white leading-none mt-2 font-display italic tracking-tight">{{ $activeVrSessions }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-[10px] font-black text-emerald-400 bg-emerald-500/5 border border-emerald-500/10 px-3 py-1.5 rounded-full w-fit uppercase tracking-widest">
            <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></div>
            Online Stream
        </div>
    </div>

    <!-- AI Tokens utilized -->
    <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-primary/30 transition-all group">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-purple-500/10 text-purple-400 rounded-2xl flex items-center justify-center border border-purple-500/20 transition-transform group-hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">Weekly Neuro Ping</span>
                @php $totalTokensWeek = collect($last7Days)->sum('ai_usage'); @endphp
                <p class="text-3xl font-black text-white leading-none mt-2 font-display italic tracking-tight">{{ number_format($totalTokensWeek) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-[10px] font-black text-purple-400 bg-purple-500/5 border border-purple-500/10 px-3 py-1.5 rounded-full w-fit uppercase tracking-widest">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            7-Day Trajectory
        </div>
    </div>

    <!-- Avg Score -->
    <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-primary/30 transition-all group">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-blue-500/10 text-blue-400 rounded-2xl flex items-center justify-center border border-blue-500/20 transition-transform group-hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">Mean Precision</span>
                <p class="text-3xl font-black text-white leading-none mt-2 font-display italic tracking-tight">{{ number_format($avgScore, 1) }}</p>
            </div>
        </div>
        <p class="text-[9px] font-black text-text-tertiary/40 uppercase tracking-[0.4em] italic leading-none">Neural Response Integrity</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
    <!-- Trends & Stats -->
    <div class="lg:col-span-2 space-y-10">
        
        <!-- Actionable Trend Chart -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
                <div>
                    <h3 class="font-black text-white text-xl tracking-tight">System Utilization Metrics</h3>
                    <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">7-Day Trajectory</p>
                </div>
            </div>
            <div class="p-8">
                <div id="dashboard-trend-chart" class="w-full h-[300px]"></div>
            </div>
        </div>

        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
                <div>
                    <h3 class="font-black text-white text-xl tracking-tight">Recent VR Telemetry</h3>
                    <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">Live feed from Meta Quest Node Cluster</p>
                </div>
                <a href="{{ route('admin.monitoring.vr') }}" class="px-6 py-2.5 bg-primary text-background rounded-xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-cyan-glow">Full Decrypt</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                        <tr>
                            <th class="px-10 py-5">Subject</th>
                            <th class="px-10 py-5">State</th>
                            <th class="px-10 py-5">Bandwidth</th>
                            <th class="px-10 py-5 text-right">Last Sync</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-divider/50">
                        @forelse($recentVrSessions as $session)
                        <tr class="hover:bg-primary/5 transition-all duration-300 group">
                            <td class="px-10 py-6">
                                <div class="text-sm font-bold text-white group-hover:text-primary transition-colors">{{ $session->user->name }}</div>
                                <div class="text-[10px] text-text-tertiary font-mono opacity-50">NODE_ID: {{ strtoupper(substr($session->device_id, 0, 8)) }}</div>
                            </td>
                            <td class="px-10 py-6">
                                @php
                                    $badgeStyle = match($session->session_status) {
                                        'starting', 'playing' => 'bg-primary/10 text-primary border-primary/20',
                                        'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        default => 'bg-surface-light text-text-tertiary border-divider'
                                    };
                                @endphp
                                <span class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $badgeStyle }}">
                                    {{ $session->session_status }}
                                </span>
                            </td>
                            <td class="px-10 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-24 bg-surface-light h-1.5 rounded-full overflow-hidden border border-divider">
                                        <div class="bg-primary h-full rounded-full shadow-cyan-glow group-hover:brightness-125 transition-all" @style(['width' => $session->progress_percentage . '%'])></div>
                                    </div>
                                    <span class="text-[10px] font-black text-white italic">{{ $session->progress_percentage }}%</span>
                                </div>
                            </td>
                            <td class="px-10 py-6 text-right text-[10px] font-bold text-text-tertiary uppercase tracking-widest opacity-60 italic">
                                {{ strtoupper($session->created_at->diffForHumans()) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-10 py-16 text-center text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic">No Active Telemetry.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Administrative Audit -->
    <div class="space-y-10">
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider bg-surface-light/30">
                <h3 class="font-black text-white text-xl tracking-tight">Governance Feed</h3>
                <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">Real-time Action Protocol</p>
            </div>
            <div class="p-8 space-y-8">
                @forelse($recentAuditLogs as $log)
                <div class="flex gap-5 group">
                    <div class="w-12 h-12 rounded-2xl bg-surface-light border border-divider flex items-center justify-center flex-shrink-0 text-xs font-black text-text-tertiary group-hover:border-primary group-hover:text-primary transition-all duration-300">
                        {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold text-white group-hover:translate-x-1 transition-transform">
                            @if($log->user)
                                <a href="{{ route('admin.reporting.user-report', $log->user) }}" class="hover:text-primary transition-colors text-text-secondary">{{ $log->user->name }}</a>
                            @else
                                <span class="text-primary font-black uppercase tracking-widest italic opacity-70">SYSTEM_KERN</span>
                            @endif
                            <span class="mx-1 text-primary/50">&rsaquo;</span>
                            <span class="font-black text-primary uppercase italic tracking-widest underline decoration-primary/20 decoration-2 underline-offset-4">{{ $log->action }}</span>
                        </div>
                        <p class="text-[9px] text-text-tertiary font-mono mt-1.5 opacity-60 uppercase tracking-tighter truncate">
                            OBJ: {{ class_basename($log->model_type) }} // HEX: #{{ strtoupper(dechex($log->model_id)) }}
                        </p>
                        <p class="text-[9px] font-black text-text-tertiary/40 uppercase tracking-[0.2em] mt-2 italic">{{ strtoupper($log->created_at->diffForHumans()) }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic py-6">Audit Trail Empty.</p>
                @endforelse
            </div>
            <div class="p-8 pt-0">
                <a href="{{ route('admin.audit-logs.index') }}" class="block w-full text-center py-5 bg-surface-light/50 hover:bg-primary hover:text-background border border-divider rounded-2xl text-[9px] font-black uppercase tracking-[0.4em] transition-all group">
                    View Complete Ledger
                </a>
            </div>
        </div>

        <!-- Quick Summary Box -->
        <div class="bg-primary rounded-4xl p-9 text-background shadow-cyan-glow relative overflow-hidden group">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/20 rounded-full blur-3xl group-hover:bg-white/30 transition-all duration-700"></div>
            
            <h4 class="font-black text-xl mb-3 font-display uppercase italic tracking-tighter">Operational Health</h4>
            <p class="text-[10px] font-bold text-background/70 mb-8 leading-relaxed uppercase tracking-widest">
                @if($health['database'] && $health['storage'])
                    Cluster integrity verified. All services responding within nominal tolerances.
                @else
                    <span class="bg-red-500 text-white px-2 py-0.5 rounded font-black italic">Alert!</span> Critical service latency detected in storage node.
                @endif
            </p>
            <div class="space-y-4">
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                    <span class="opacity-60 italic">Core Database</span>
                    <span class="bg-background text-primary px-3 py-1 rounded-full border border-primary/20 {{ $health['database'] ? '' : 'text-red-400' }}">{{ $health['database'] ? 'Synced' : 'Error' }}</span>
                </div>
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                    <span class="opacity-60 italic">Storage Array</span>
                    <span class="bg-background text-primary px-3 py-1 rounded-full border border-primary/20 {{ $health['storage'] ? '' : 'text-red-400' }}">{{ $health['storage'] ? 'Nominal' : 'Error' }}</span>
                </div>
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                    <span class="opacity-60 italic">Uptime Log</span>
                    <span class="bg-background text-primary px-3 py-1 rounded-full border border-primary/20">{{ $health['uptime'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var trendData = {!! json_encode($last7Days) !!};
        
        var options = {
            series: [{
                name: 'VR Sessions',
                data: trendData.map(d => d.vr_sessions)
            }, {
                name: 'Neuro Invocations',
                data: trendData.map(d => d.ai_usage)
            }, {
                name: 'Assessments',
                data: trendData.map(d => d.assessments)
            }],
            chart: {
                type: 'area',
                height: 300,
                background: 'transparent',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#00E5FF', '#A855F7', '#10B981'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.0,
                    stops: [0, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                categories: trendData.map(d => d.date),
                labels: { style: { colors: '#8FA1B4', fontSize: '9px', fontFamily: 'Orbitron, sans-serif' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#E5E7EB', fontWeight: 800, fontFamily: 'Orbitron, sans-serif' } },
            },
            tooltip: {
                theme: 'dark'
            },
            grid: {
                borderColor: 'rgba(255,255,255,0.05)',
                strokeDashArray: 4,
            },
            legend: {
                position: 'top',
                labels: { colors: '#E5E7EB' },
                fontFamily: 'Inter, sans-serif',
                fontWeight: 800,
                textTransform: 'uppercase'
            }
        };

        var chart = new ApexCharts(document.querySelector("#dashboard-trend-chart"), options);
        chart.render();
    });
</script>
@endpush
