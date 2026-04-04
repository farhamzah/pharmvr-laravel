@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.advanced-reports.hub') }}" class="hover:text-primary transition-colors">Analytics Hub</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Item Analysis</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Question Efficacy Rank</h1>
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
                    <option value="">All Clusters (GLOBAL)</option>
                    @foreach($modules as $id => $title)
                        <option value="{{ $id }}" {{ request('module_id') == $id ? 'selected' : '' }}>MOD_{{ $id }} - {{ $title }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-8 py-3 bg-surface-light border border-divider hover:border-primary/50 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">
                Execute Filter
            </button>
            @if(request('module_id'))
                <a href="{{ route('admin.advanced-reports.question-analysis') }}" class="px-8 py-3 text-red-400 hover:bg-red-500/10 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">Clear</a>
            @endif
        </form>

        <button onclick="exportData()" class="px-6 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-cyan-glow flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export Sync Stream
        </button>
    </div>

    @if($results->isEmpty())
        <div class="bg-surface rounded-4xl border border-divider p-16 text-center">
            <div class="w-20 h-20 bg-surface-light rounded-full flex items-center justify-center mx-auto mb-6 text-text-tertiary">
                <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-white font-black text-lg font-display uppercase italic tracking-wider mb-2">No Signal Detected</h3>
            <p class="text-text-tertiary text-sm">No answering data found for the current query matrix.</p>
        </div>
    @else
        <!-- Chart Section -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 relative overflow-hidden">
            <div class="flex justify-between items-center border-b border-divider pb-4 mb-6">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display">Bottom 20 Questions (Highest Fail Rate)</h3>
                <span class="text-[9px] font-black text-primary uppercase tracking-[0.3em] px-3 py-1 bg-primary/10 rounded-full border border-primary/20">Critical Attention Required</span>
            </div>
            <div id="chart-questions" class="w-full h-[450px]"></div>
        </div>

        <!-- Data Table -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider bg-surface-light/30">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display">Global Question Analysis Matrix</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                        <tr>
                            <th class="px-8 py-5">Item Identifier</th>
                            <th class="px-8 py-5">Module Cluster</th>
                            <th class="px-8 py-5 text-center">Total Responses</th>
                            <th class="px-8 py-5 text-center">Accuracy Index</th>
                            <th class="px-8 py-5 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-divider/50">
                        @foreach($results->sortBy('correct_rate') as $row)
                        <tr class="hover:bg-primary/5 transition-colors duration-300">
                            <td class="px-8 py-5">
                                <div class="text-[10px] text-primary font-mono opacity-80 mb-1">Q_ID_{{ $row->question_id }}</div>
                                <div class="text-sm font-bold text-white max-w-xl truncate">{{ $row->question_text }}</div>
                            </td>
                            <td class="px-8 py-5 text-xs text-text-secondary font-bold">{{ $row->module_title }}</td>
                            <td class="px-8 py-5 text-center font-display italic text-text-secondary">{{ $row->total_answers }}</td>
                            <td class="px-8 py-5 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <span class="font-display italic text-white text-lg">{{ $row->correct_rate }}%</span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                @if($row->correct_rate < 40)
                                    <span class="px-3 py-1 bg-red-500/10 text-red-500 border border-red-500/20 rounded-full text-[9px] font-black uppercase tracking-widest">Awaiting Re-tune</span>
                                @elseif($row->correct_rate < 75)
                                    <span class="px-3 py-1 bg-orange-500/10 text-orange-400 border border-orange-500/20 rounded-full text-[9px] font-black uppercase tracking-widest">Nominal</span>
                                @else
                                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[9px] font-black uppercase tracking-widest">Optimal</span>
                                @endif
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
            var categories = {!! json_encode($chartData->pluck('question_id')->map(fn($id) => 'Q_'.$id)) !!};
            var dataCorrect = {!! json_encode($chartData->pluck('correct_rate')) !!};
            
            var options = {
                series: [{
                    name: 'Accuracy Index',
                    data: dataCorrect
                }],
                chart: {
                    type: 'bar',
                    height: 450,
                    background: 'transparent',
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '50%',
                        borderRadius: 4,
                        colors: {
                            ranges: [{
                                from: 0,
                                to: 40,
                                color: '#EF4444' // Red - Critical
                            }, {
                                from: 40.1,
                                to: 75,
                                color: '#F97316' // Orange - Warning
                            }, {
                                from: 75.1,
                                to: 100,
                                color: '#10B981' // Green - Good
                            }]
                        }
                    }
                },
                colors: ['#00E5FF'],
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    style: {
                        colors: ['#fff'],
                        fontFamily: 'Orbitron, sans-serif',
                        fontSize: '10px'
                    },
                    formatter: function (val, opt) {
                        return val + "%"
                    },
                    offsetX: 0,
                },
                stroke: { show: true, width: 1, colors: ['transparent'] },
                xaxis: {
                    categories: categories,
                    labels: { style: { colors: '#8FA1B4', fontFamily: 'Orbitron, sans-serif' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    max: 100
                },
                yaxis: {
                    labels: { style: { colors: '#E5E7EB', fontWeight: 800, fontSize: '11px', fontFamily: 'Orbitron, sans-serif' } },
                },
                fill: { opacity: 1 },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: function (val) { return val + "% Precision" } }
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.05)',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } }
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart-questions"), options);
            chart.render();
        @endif
    });

    function exportData() {
        fetch("{{ route('admin.advanced-reports.export-csv', ['type' => 'question-analysis']) }}")
            .then(res => res.json())
            .then(data => alert('Export pipeline triggered: ' + data.message));
    }
</script>
@endpush
