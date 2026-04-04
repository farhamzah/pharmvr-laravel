@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.advanced-reports.hub') }}" class="hover:text-primary transition-colors">Analytics Hub</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Journey Funnel</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Completion Pipeline</h1>
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
                <a href="{{ route('admin.advanced-reports.completion-funnel') }}" class="px-8 py-3 text-red-400 hover:bg-red-500/10 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">Clear</a>
            @endif
        </form>

        <button onclick="exportData()" class="px-6 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-cyan-glow flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export Sync Stream
        </button>
    </div>

    @if($funnelData['Total Enrolled'] == 0)
        <div class="bg-surface rounded-4xl border border-divider p-16 text-center">
            <div class="w-20 h-20 bg-surface-light rounded-full flex items-center justify-center mx-auto mb-6 text-text-tertiary">
                <svg class="w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            </div>
            <h3 class="text-white font-black text-lg font-display uppercase italic tracking-wider mb-2">No Signal Detected</h3>
            <p class="text-text-tertiary text-sm">No enrollment or progress data available for the selected parameters.</p>
        </div>
    @else
        <!-- Funnel Visualization Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-surface rounded-4xl border border-divider shadow-premium p-8 relative overflow-hidden">
                <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-6">Drop-Off Trajectory</h3>
                <div id="chart-funnel" class="w-full h-[400px]"></div>
            </div>

            <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 flex flex-col justify-center relative overflow-hidden">
                <div class="absolute -right-20 -bottom-20 w-48 h-48 bg-primary/10 rounded-full blur-[80px] pointer-events-none"></div>
                
                <h3 class="font-black text-text-tertiary text-[10px] uppercase tracking-[0.3em] mb-8 text-center">Conversion Metrics</h3>
                
                <div class="flex flex-col items-center justify-center space-y-8">
                    <div class="text-center">
                        <div class="text-[10px] font-black text-text-tertiary uppercase tracking-widest mb-1">Overall Completion Rate</div>
                        @php
                            $rate = $funnelData['Total Enrolled'] > 0 
                                ? round(($funnelData['Certified/Completed'] / $funnelData['Total Enrolled']) * 100, 1) 
                                : 0;
                        @endphp
                        <div class="text-5xl font-black text-white font-display tracking-tighter">{{ $rate }}<span class="text-xl text-primary">%</span></div>
                    </div>

                    <div class="w-full h-px bg-divider"></div>

                    <div class="text-center">
                        <div class="text-[10px] font-black text-text-tertiary uppercase tracking-widest mb-1">Critical Drop-off Phase</div>
                        @php
                            // Calculate where the biggest drop happens
                            $drops = [
                                'Pre-test Phase' => $funnelData['Total Enrolled'] - $funnelData['Passed Pre-test'],
                                'VR Execution' => $funnelData['Passed Pre-test'] - $funnelData['Completed VR Step'],
                                'Post-test Phase' => $funnelData['Completed VR Step'] - $funnelData['Passed Post-test'],
                                'Final Cert' => $funnelData['Passed Post-test'] - $funnelData['Certified/Completed'],
                            ];
                            $maxDrop = max($drops);
                            $criticalPhase = array_search($maxDrop, $drops);
                        @endphp
                        <div class="text-lg font-black text-red-400 font-display tracking-widest uppercase italic">{{ $criticalPhase }}</div>
                        <div class="text-xs text-text-tertiary font-medium mt-1">{{ $maxDrop }} subjects lost</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 overflow-x-auto">
            <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display mb-6">Pipeline Data Table</h3>
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                    <tr>
                        <th class="px-8 py-5">Phase Name</th>
                        <th class="px-8 py-5 text-center">Subjects Remaining</th>
                        <th class="px-8 py-5 text-right">Step Retention</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/50">
                    @php 
                        $prevValue = $funnelData['Total Enrolled'];
                        $steps = array_keys($funnelData);
                        $values = array_values($funnelData);
                    @endphp
                    @foreach($funnelData as $key => $value)
                        @php 
                            $stepRetention = $prevValue > 0 ? round(($value / $prevValue) * 100, 1) : 0;
                            $prevValue = $value;
                        @endphp
                        <tr class="hover:bg-primary/5 transition-colors duration-300">
                            <td class="px-8 py-5 text-sm font-bold text-white uppercase tracking-wider">{{ $key }}</td>
                            <td class="px-8 py-5 text-center font-display italic text-text-secondary">{{ $value }}</td>
                            <td class="px-8 py-5 text-right">
                                <span class="px-3 py-1 bg-surface-light border border-divider rounded-full text-[10px] font-black {{ $stepRetention < 50 && $loop->index > 0 ? 'text-red-400 border-red-500/20 bg-red-500/10' : 'text-primary' }} tracking-widest">
                                    {{ $stepRetention }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($funnelData['Total Enrolled'] > 0)
            var funnelKeys = {!! json_encode(array_keys($funnelData)) !!};
            var funnelValues = {!! json_encode(array_values($funnelData)) !!};
            
            var options = {
                series: [{
                    name: 'Subjects Active',
                    data: funnelValues
                }],
                chart: {
                    type: 'bar',
                    height: 400,
                    background: 'transparent',
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 0,
                        horizontal: true,
                        barHeight: '80%',
                        isFunnel: true,
                        colors: {
                            ranges: [
                                { from: 0, to: 10000, color: '#00E5FF' }
                            ]
                        }
                    },
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opt) {
                        return opt.w.globals.labels[opt.dataPointIndex] + ':  ' + val
                    },
                    dropShadow: {
                        enabled: true,
                        top: 1,
                        left: 1,
                        blur: 1,
                        color: '#000',
                        opacity: 0.8
                    },
                    style: {
                        colors: ['#fff'],
                        fontFamily: 'Orbitron, sans-serif',
                        fontSize: '11px',
                        fontWeight: 900
                    }
                },
                xaxis: {
                    categories: funnelKeys,
                    labels: { show: false },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    show: false
                },
                fill: {
                    opacity: 1,
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.5,
                        gradientToColors: ['#3A4D63'],
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 100]
                    }
                },
                grid: { show: false },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: function (val) { return val + " subjects" } }
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart-funnel"), options);
            chart.render();
        @endif
    });

    function exportData() {
        fetch("{{ route('admin.advanced-reports.export-csv', ['type' => 'completion-funnel']) }}")
            .then(res => res.json())
            .then(data => alert('Export pipeline triggered: ' + data.message));
    }
</script>
@endpush
