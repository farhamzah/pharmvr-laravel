@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.advanced-reports.hub') }}" class="hover:text-primary transition-colors">Analytics Hub</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Knowledge Gain</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Pre-Test vs Post-Test</h1>
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
                <a href="{{ route('admin.advanced-reports.pretest-posttest') }}" class="px-8 py-3 text-red-400 hover:bg-red-500/10 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">Clear</a>
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
                <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <h3 class="text-white font-black text-lg font-display uppercase italic tracking-wider mb-2">No Signal Detected</h3>
            <p class="text-text-tertiary text-sm">No paired pre-test and post-test data found for the current filter parameters.</p>
        </div>
    @else
        <!-- Chart Section -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 relative overflow-hidden">
            <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-6">Aggregate Learning Progression</h3>
            <div id="chart-pre-post" class="w-full h-[400px]"></div>
        </div>

        <!-- Data Table -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
            <div class="p-8 border-b border-divider bg-surface-light/30">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display">Individual Data Matrix</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                        <tr>
                            <th class="px-8 py-5">Operator</th>
                            <th class="px-8 py-5">Module Cluster</th>
                            <th class="px-8 py-5 text-center">Initial Scan (Pre)</th>
                            <th class="px-8 py-5 text-center">Final Scan (Post)</th>
                            <th class="px-8 py-5 text-right">Delta (Gain)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-divider/50">
                        @foreach($results as $row)
                        <tr class="hover:bg-primary/5 transition-colors duration-300">
                            <td class="px-8 py-5">
                                <div class="text-sm font-bold text-white">{{ $row->user_name }}</div>
                                <div class="text-[10px] text-text-tertiary font-mono opacity-50">{{ $row->user_email }}</div>
                            </td>
                            <td class="px-8 py-5 text-xs text-text-secondary font-bold">{{ $row->module_title }}</td>
                            <td class="px-8 py-5 text-center font-display italic text-text-secondary">{{ $row->pretest_score }}</td>
                            <td class="px-8 py-5 text-center font-display italic text-white text-lg">{{ $row->posttest_score }}</td>
                            <td class="px-8 py-5 text-right">
                                @if($row->score_gain > 0)
                                    <span class="inline-flex items-center gap-1 text-emerald-400 font-display italic font-black">
                                        +{{ $row->score_gain }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                    </span>
                                @elseif($row->score_gain < 0)
                                    <span class="inline-flex items-center gap-1 text-red-400 font-display italic font-black">
                                        {{ $row->score_gain }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                    </span>
                                @else
                                    <span class="text-text-tertiary font-display italic font-black">0</span>
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
        @if($moduleStats->isNotEmpty())
            var options = {
                series: [{
                    name: 'Pre-Test Average',
                    data: {!! json_encode($moduleStats->pluck('avg_pretest')) !!}
                }, {
                    name: 'Post-Test Average',
                    data: {!! json_encode($moduleStats->pluck('avg_posttest')) !!}
                }],
                chart: {
                    type: 'bar',
                    height: 400,
                    background: 'transparent',
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                colors: ['#3A4D63', '#00E5FF'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '45%',
                        endingShape: 'rounded',
                        borderRadius: 4
                    },
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: {
                    categories: {!! json_encode($moduleStats->pluck('module_title')) !!},
                    labels: { style: { colors: '#8FA1B4', fontSize: '10px', fontFamily: 'Orbitron, sans-serif', cssClass: 'uppercase tracking-widest' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    max: 100,
                    labels: { style: { colors: '#8FA1B4', fontFamily: 'Orbitron, sans-serif' } },
                },
                fill: { opacity: 1 },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: function (val) { return val + " pts" } }
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.05)',
                    strokeDashArray: 4,
                },
                legend: {
                    labels: { colors: '#E5E7EB' },
                    fontFamily: 'Inter, sans-serif',
                    fontWeight: 800,
                    textTransform: 'uppercase'
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart-pre-post"), options);
            chart.render();
        @endif
    });

    function exportData() {
        // Fallback or Trigger API Export
        fetch("{{ route('admin.advanced-reports.export-csv', ['type' => 'pretest-posttest']) }}")
            .then(res => res.json())
            .then(data => alert('Export pipeline triggered: ' + data.message));
    }
</script>
@endpush
