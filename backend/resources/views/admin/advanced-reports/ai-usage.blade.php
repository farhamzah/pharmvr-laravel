@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.advanced-reports.hub') }}" class="hover:text-primary transition-colors">Analytics Hub</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Neural Metrics</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">AI Usage & Cost Monitor</h1>
</div>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Filter -->
    <div class="bg-surface rounded-3xl border border-divider shadow-premium p-6 flex flex-wrap gap-4 items-end justify-between">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">Time Horizon</label>
                <select name="days" class="px-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none shadow-sm min-w-[200px]">
                    <option value="7" {{ request('days') == '7' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ request('days', '30') == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ request('days') == '90' ? 'selected' : '' }}>Last 90 Days</option>
                </select>
            </div>
            <button type="submit" class="px-8 py-3 bg-surface-light border border-divider hover:border-primary/50 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">
                Execute Query
            </button>
        </form>

        <button onclick="exportData()" class="px-6 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-cyan-glow flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export Sync Stream
        </button>
    </div>

    <!-- KPI Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-surface rounded-3xl border border-divider shadow-premium p-6 flex flex-col justify-between group hover:border-primary/50 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="text-[10px] font-black text-primary uppercase tracking-[0.3em] opacity-80">Total Interactions</div>
                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
            </div>
            <div class="text-4xl font-display font-black text-white italic tracking-tighter">{{ number_format($summary['total_interactions']) }}</div>
        </div>

        <div class="bg-surface rounded-3xl border border-divider shadow-premium p-6 flex flex-col justify-between group hover:border-purple-500/50 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="text-[10px] font-black text-purple-400 uppercase tracking-[0.3em] opacity-80">Accumulated Tokens</div>
                <div class="w-8 h-8 rounded-full bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
            <div class="text-4xl font-display font-black text-white italic tracking-tighter">{{ number_format($summary['total_tokens']) }}</div>
        </div>

        <div class="bg-surface rounded-3xl border border-divider shadow-premium p-6 flex flex-col justify-between group hover:border-cyan-500/50 transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.3em] opacity-80">Average Latency</div>
                <div class="w-8 h-8 rounded-full bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="text-4xl font-display font-black text-white italic tracking-tighter">{{ $summary['avg_latency'] }}<span class="text-xl text-text-tertiary">ms</span></div>
        </div>

        <div class="bg-surface rounded-3xl border border-divider shadow-premium p-6 flex flex-col justify-between {{ $summary['flagged_responses'] > 0 ? 'bg-red-500/5 border-red-500/30' : 'group hover:border-emerald-500/50' }} transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
                <div class="text-[10px] font-black {{ $summary['flagged_responses'] > 0 ? 'text-red-500' : 'text-emerald-500' }} uppercase tracking-[0.3em] opacity-80">Safety Flags</div>
                <div class="w-8 h-8 rounded-full {{ $summary['flagged_responses'] > 0 ? 'bg-red-500/10 text-red-500' : 'bg-emerald-500/10 text-emerald-500' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="text-4xl font-display font-black text-white italic tracking-tighter">{{ $summary['flagged_responses'] }}</div>
        </div>
    </div>

    @if($dailyTokens->isEmpty())
        <div class="bg-surface rounded-4xl border border-divider p-16 text-center shadow-premium">
            <div class="w-20 h-20 bg-surface-light rounded-full flex items-center justify-center mx-auto mb-6 text-text-tertiary">
                <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <h3 class="text-white font-black text-lg font-display uppercase italic tracking-wider mb-2">No Signal Detected</h3>
            <p class="text-text-tertiary text-sm">No neural telemetry recorded in the selected chronological window.</p>
        </div>
    @else
        <!-- Daily Area Chart -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 relative overflow-hidden">
            <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-6">Token Burn Rate Trajectory</h3>
            <div id="chart-tokens" class="w-full h-[400px]"></div>
        </div>

        <!-- Demographics / Splits -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 overflow-hidden">
                <h3 class="font-black text-white text-sm tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-6">Interaction Typology Distribution</h3>
                <div id="chart-pie-types" class="w-full h-[300px]"></div>
            </div>
            <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 overflow-hidden">
                <h3 class="font-black text-white text-sm tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-6">Model Distribution Factor</h3>
                <div id="chart-pie-models" class="w-full h-[300px]"></div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($dailyTokens->isNotEmpty())
            // Timeline Area Chart
            var tokenKeys = {!! json_encode(array_keys($dailyTokens->toArray())) !!};
            var tokenValues = {!! json_encode(array_values($dailyTokens->toArray())) !!};
            
            var optionsArea = {
                series: [{
                    name: 'Tokens Burned',
                    data: tokenValues
                }],
                chart: {
                    type: 'area',
                    height: 400,
                    background: 'transparent',
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                    zoom: { enabled: false }
                },
                colors: ['#A855F7'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.0,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 3, curve: 'smooth' },
                xaxis: {
                    categories: tokenKeys,
                    labels: { style: { colors: '#8FA1B4', fontSize: '10px', fontFamily: 'Orbitron, sans-serif' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#E5E7EB', fontWeight: 800, fontFamily: 'Orbitron, sans-serif' } },
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.05)',
                    strokeDashArray: 4,
                },
                tooltip: {
                    theme: 'dark'
                }
            };
            var chartArea = new ApexCharts(document.querySelector("#chart-tokens"), optionsArea);
            chartArea.render();

            // Types Pie Chart
            var typeOptions = {
                series: {!! json_encode(array_values($typeStats->toArray())) !!},
                labels: {!! json_encode(array_keys($typeStats->toArray())) !!},
                chart: { type: 'donut', height: 300, background: 'transparent' },
                colors: ['#00E5FF', '#10B981', '#A855F7', '#F59E0B', '#EF4444'],
                stroke: { width: 0 },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: { color: '#8FA1B4', fontFamily: 'Orbitron, sans-serif' },
                                value: { color: '#fff', fontSize: '24px', fontFamily: 'Inter, sans-serif', fontWeight: 900 }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: { colors: '#E5E7EB' },
                    fontFamily: 'Inter, sans-serif',
                    fontWeight: 800
                },
                tooltip: { theme: 'dark' }
            };
            var chartPieTypes = new ApexCharts(document.querySelector("#chart-pie-types"), typeOptions);
            chartPieTypes.render();

            // Models Pie Chart
            var modelOptions = {
                series: {!! json_encode(array_values($modelStats->toArray())) !!},
                labels: {!! json_encode(array_keys($modelStats->toArray())) !!},
                chart: { type: 'donut', height: 300, background: 'transparent' },
                colors: ['#3A4D63', '#4B5563', '#6B7280', '#9CA3AF', '#D1D5DB'],
                stroke: { width: 0 },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: { color: '#8FA1B4', fontFamily: 'Orbitron, sans-serif' },
                                value: { color: '#fff', fontSize: '24px', fontFamily: 'Inter, sans-serif', fontWeight: 900 }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: { colors: '#E5E7EB' },
                    fontFamily: 'Inter, sans-serif',
                    fontWeight: 800
                },
                tooltip: { theme: 'dark' }
            };
            var chartPieModels = new ApexCharts(document.querySelector("#chart-pie-models"), modelOptions);
            chartPieModels.render();
        @endif
    });

    function exportData() {
        fetch("{{ route('admin.advanced-reports.export-csv', ['type' => 'ai-usage']) }}")
            .then(res => res.json())
            .then(data => alert('Export pipeline triggered: ' + data.message));
    }
</script>
@endpush
