@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.advanced-reports.hub') }}" class="hover:text-primary transition-colors">Analytics Hub</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Activity Trends</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Chronological System Utilization</h1>
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
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
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

    @if($trendData->isEmpty())
        <div class="bg-surface rounded-4xl border border-divider p-16 text-center shadow-premium">
            <div class="w-20 h-20 bg-surface-light rounded-full flex items-center justify-center mx-auto mb-6 text-text-tertiary">
                <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            </div>
            <h3 class="text-white font-black text-lg font-display uppercase italic tracking-wider mb-2">No Signal Detected</h3>
            <p class="text-text-tertiary text-sm">No activity events recorded in the selected chronological window.</p>
        </div>
    @else
        <!-- Composite Line Chart Section -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 relative overflow-hidden">
            <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-6">Cross-System Action Trajectory</h3>
            <div id="chart-trends" class="w-full h-[450px]"></div>
        </div>

        <!-- Metric Cards -->
        @php
            $totalVr = $trendData->sum('vr_sessions');
            $totalAi = $trendData->sum('ai_usage');
            $totalAsmt = $trendData->sum('assessments');
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-surface-light border border-divider rounded-3xl p-6 group transition-all duration-300 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-cyan-500/10 rounded-full blur-[40px]"></div>
                <div class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.3em] mb-2 opacity-80">VR Sim Executions</div>
                <div class="text-3xl font-display font-black text-white italic tracking-tighter">{{ $totalVr }}<span class="text-sm ml-1 text-text-tertiary">SESS</span></div>
            </div>
            
            <div class="bg-surface-light border border-divider rounded-3xl p-6 group transition-all duration-300 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-[40px]"></div>
                <div class="text-[10px] font-black text-emerald-400 uppercase tracking-[0.3em] mb-2 opacity-80">Assessment Submissions</div>
                <div class="text-3xl font-display font-black text-white italic tracking-tighter">{{ $totalAsmt }}<span class="text-sm ml-1 text-text-tertiary">EVAL</span></div>
            </div>

            <div class="bg-surface-light border border-divider rounded-3xl p-6 group transition-all duration-300 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-purple-500/10 rounded-full blur-[40px]"></div>
                <div class="text-[10px] font-black text-purple-400 uppercase tracking-[0.3em] mb-2 opacity-80">AI Guide Invocations</div>
                <div class="text-3xl font-display font-black text-white italic tracking-tighter">{{ $totalAi }}<span class="text-sm ml-1 text-text-tertiary">PING</span></div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden mt-8">
            <div class="p-8 border-b border-divider bg-surface-light/30">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display">Daily Timeline Log</h3>
            </div>
            <div class="overflow-x-auto max-h-[500px]">
                <table class="w-full text-left border-collapse relative">
                    <thead class="bg-surface text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-8 py-5">Date Reference</th>
                            <th class="px-8 py-5 text-center text-cyan-400">VR Sessions</th>
                            <th class="px-8 py-5 text-center text-emerald-400">Assessments</th>
                            <th class="px-8 py-5 text-right text-purple-400">AI Invocations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-divider/50">
                        @foreach($trendData->reverse() as $row)
                        <tr class="hover:bg-primary/5 transition-colors duration-300">
                            <td class="px-8 py-5 text-xs font-bold text-white tracking-wider font-display">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                            <td class="px-8 py-5 text-center font-display italic font-black text-white">{{ $row['vr_sessions'] ?: '-' }}</td>
                            <td class="px-8 py-5 text-center font-display italic font-black text-white">{{ $row['assessments'] ?: '-' }}</td>
                            <td class="px-8 py-5 text-right font-display italic font-black text-white">{{ $row['ai_usage'] ?: '-' }}</td>
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
        @if($trendData->isNotEmpty())
            var categories = {!! json_encode($trendData->pluck('date')) !!};
            var valVr = {!! json_encode($trendData->pluck('vr_sessions')) !!};
            var valAi = {!! json_encode($trendData->pluck('ai_usage')) !!};
            var valAsmt = {!! json_encode($trendData->pluck('assessments')) !!};
            
            var options = {
                series: [
                    { name: 'VR Sessions', data: valVr },
                    { name: 'Assessments', data: valAsmt },
                    { name: 'AI Uses', data: valAi }
                ],
                chart: {
                    type: 'area',
                    height: 450,
                    background: 'transparent',
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                colors: ['#00E5FF', '#10B981', '#A855F7'],
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.2,
                        opacityTo: 0.0,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    categories: categories,
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
            var chart = new ApexCharts(document.querySelector("#chart-trends"), options);
            chart.render();
        @endif
    });

    function exportData() {
        fetch("{{ route('admin.advanced-reports.export-csv', ['type' => 'trends']) }}")
            .then(res => res.json())
            .then(data => alert('Export pipeline triggered: ' + data.message));
    }
</script>
@endpush
