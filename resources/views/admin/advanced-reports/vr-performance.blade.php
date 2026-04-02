@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.advanced-reports.hub') }}" class="hover:text-primary transition-colors">Analytics Hub</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">VR Operations</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Simulation Performance Profiler</h1>
</div>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Filter -->
    <div class="bg-surface rounded-3xl border border-divider shadow-premium p-6 flex flex-wrap gap-4 items-end justify-between">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">Training Cluster</label>
                <select name="module_id" class="px-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none shadow-sm min-w-[250px]">
                    <option value="">Aggregate All Modules</option>
                    @foreach($modules as $id => $title)
                        <option value="{{ $id }}" {{ request('module_id') == $id ? 'selected' : '' }}>MOD_{{ $id }} - {{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-8 py-3 bg-surface-light border border-divider hover:border-primary/50 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">
                Execute Filter
            </button>
            @if(request('module_id'))
                <a href="{{ route('admin.advanced-reports.vr-performance') }}" class="px-8 py-3 text-red-400 hover:bg-red-500/10 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">Clear</a>
            @endif
        </form>

        <button onclick="exportData()" class="px-6 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-cyan-glow flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export Sync Stream
        </button>
    </div>

    @if($sessions->isEmpty())
        <div class="bg-surface rounded-4xl border border-divider p-16 text-center">
            <div class="w-20 h-20 bg-surface-light rounded-full flex items-center justify-center mx-auto mb-6 text-text-tertiary">
                <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
            <h3 class="text-white font-black text-lg font-display uppercase italic tracking-wider mb-2">No Signal Detected</h3>
            <p class="text-text-tertiary text-sm">No VR telemetry data found for the current filter parameters.</p>
        </div>
    @else
        <!-- Radar Chart Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 relative overflow-hidden flex flex-col justify-center items-center">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-6 w-full text-center">Tri-Axis Skill Profiler</h3>
                <div id="chart-radar" class="w-full max-w-[500px]"></div>
            </div>

            <!-- Summary Stats -->
            <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 relative flex flex-col justify-center space-y-6 overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-[100px] pointer-events-none"></div>
                
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-2">Global Simulation Averages</h3>

                @php
                    $avgAccuracy = round($sessions->avg('analytics.accuracy_score'), 1);
                    $avgSpeed = round($sessions->avg('analytics.speed_score'), 1);
                    $avgTotal = round($sessions->avg('analytics.total_score'), 1);
                    $totalBreaches = $sessions->sum('analytics.breach_count');
                @endphp

                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-surface-light border border-divider rounded-3xl p-6 group hover:border-emerald-500/50 transition-all duration-300">
                        <div class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em] mb-2 opacity-80">Accuracy Matrix</div>
                        <div class="text-4xl font-display font-black text-white italic tracking-tighter">{{ $avgAccuracy }}<span class="text-lg text-text-tertiary">%</span></div>
                    </div>
                    <div class="bg-surface-light border border-divider rounded-3xl p-6 group hover:border-cyan-500/50 transition-all duration-300">
                        <div class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.3em] mb-2 opacity-80">Operational Velocity</div>
                        <div class="text-4xl font-display font-black text-white italic tracking-tighter">{{ $avgSpeed }}<span class="text-lg text-text-tertiary">pts</span></div>
                    </div>
                </div>

                <div class="bg-red-500/10 border border-red-500/20 rounded-3xl p-6 flex justify-between items-center group hover:bg-red-500/20 transition-all duration-300">
                    <div>
                        <div class="text-[10px] font-black text-red-500 uppercase tracking-[0.3em] mb-1">Total Protocol Breaches</div>
                        <div class="text-xs text-text-tertiary">Across all filtered simulations</div>
                    </div>
                    <div class="text-4xl font-display font-black text-red-400 italic tracking-tighter">{{ $totalBreaches }}</div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider bg-surface-light/30">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display">Simulation Telemetry Matrix</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                        <tr>
                            <th class="px-8 py-5">Session ID</th>
                            <th class="px-8 py-5">Operator</th>
                            <th class="px-8 py-5">Module Cluster</th>
                            <th class="px-8 py-5 text-center">Score</th>
                            <th class="px-8 py-5 text-center">Accuracy</th>
                            <th class="px-8 py-5 text-center">Velocity</th>
                            <th class="px-8 py-5 text-right">Breaches</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-divider/50">
                        @foreach($sessions as $session)
                        <tr class="hover:bg-primary/5 transition-colors duration-300">
                            <td class="px-8 py-5 text-[10px] font-mono font-black text-primary opacity-80">SESS_{{ substr($session->id, 0, 8) }}</td>
                            <td class="px-8 py-5 text-sm font-bold text-white tracking-wider">{{ $session->user->name ?? 'Unknown' }}</td>
                            <td class="px-8 py-5 text-xs text-text-secondary font-bold">{{ $session->trainingModule ? $session->trainingModule->title : 'Unknown' }}</td>
                            <td class="px-8 py-5 text-center font-display italic font-black text-white text-lg">{{ $session->analytics->total_score ?? 0 }}</td>
                            <td class="px-8 py-5 text-center">
                                <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[10px] font-black font-display italic tracking-widest">{{ $session->analytics->accuracy_score ?? 0 }}%</span>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span class="px-3 py-1 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded-full text-[10px] font-black font-display italic tracking-widest">{{ $session->analytics->speed_score ?? 0 }}</span>
                            </td>
                            <td class="px-8 py-5 text-right font-display italic font-black {{ ($session->analytics->breach_count ?? 0) > 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                {{ $session->analytics->breach_count ?? 0 }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($chartData->isNotEmpty())
            var categories = {!! json_encode($chartData->pluck('module_title')->map(function($title) { return substr($title, 0, 15) . '...'; })) !!};
            var dataSpeed = {!! json_encode($chartData->pluck('avg_speed_score')) !!};
            var dataAccuracy = {!! json_encode($chartData->pluck('avg_accuracy_score')) !!};
            
            var options = {
                series: [{
                    name: 'Velocity Index',
                    data: dataSpeed,
                }, {
                    name: 'Accuracy Matrix',
                    data: dataAccuracy,
                }],
                chart: {
                    height: 400,
                    type: 'radar',
                    background: 'transparent',
                    toolbar: { show: false },
                    fontFamily: 'Orbitron, sans-serif'
                },
                colors: ['#00E5FF', '#10B981'],
                stroke: {
                    width: 2,
                    dashArray: 0
                },
                fill: {
                    opacity: 0.2
                },
                markers: {
                    size: 4,
                    colors: ['#00E5FF', '#10B981'],
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: { size: 7 }
                },
                xaxis: {
                    categories: categories,
                    labels: { style: { colors: ['#8FA1B4', '#8FA1B4', '#8FA1B4', '#8FA1B4', '#8FA1B4', '#8FA1B4'], fontSize: '9px', fontWeight: 900 } }
                },
                yaxis: {
                    show: false,
                    max: 100
                },
                tooltip: {
                    theme: 'dark'
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.05)',
                },
                legend: {
                    labels: { colors: '#E5E7EB' },
                    fontFamily: 'Inter, sans-serif',
                    fontWeight: 800,
                    textTransform: 'uppercase'
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart-radar"), options);
            chart.render();
        @endif
    });

    function exportData() {
        fetch("{{ route('admin.advanced-reports.export-csv', ['type' => 'vr-performance']) }}")
            .then(res => res.json())
            .then(data => alert('Export pipeline triggered: ' + data.message));
    }
</script>
@endpush
