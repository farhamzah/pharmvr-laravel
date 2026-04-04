@extends('layouts.admin')

@section('header', 'Assessment Intelligence')

@section('content')
{{-- Hero Stats Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    {{-- Total Attempts --}}
    <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-primary/30 transition-all group">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-primary/10 text-primary rounded-2xl flex items-center justify-center border border-primary/20 transition-transform group-hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">Total Submissions</span>
                <p class="text-3xl font-black text-white leading-none mt-2 font-display italic tracking-tight">{{ number_format($totalAttempts) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-[10px] font-black text-primary bg-primary/5 border border-primary/10 px-3 py-1.5 rounded-full w-fit uppercase tracking-widest">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            {{ $uniqueStudents }} Peserta
        </div>
    </div>

    {{-- Pass Rate --}}
    <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-emerald-500/30 transition-all group">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-emerald-500/10 text-emerald-400 rounded-2xl flex items-center justify-center border border-emerald-500/20 transition-transform group-hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">Pass Rate</span>
                <p class="text-3xl font-black text-emerald-400 leading-none mt-2 font-display italic tracking-tight">{{ $totalAttempts > 0 ? number_format(($passedCount / $totalAttempts) * 100, 1) : 0 }}%</p>
            </div>
        </div>
        <div class="w-full bg-surface-light h-1.5 rounded-full overflow-hidden border border-divider">
            <div class="bg-emerald-400 h-full rounded-full" @style(['width' => ($totalAttempts > 0 ? ($passedCount / $totalAttempts) * 100 : 0) . '%'])></div>
        </div>
    </div>

    {{-- Pre-Test Average --}}
    <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-blue-500/30 transition-all group">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-blue-500/10 text-blue-400 rounded-2xl flex items-center justify-center border border-blue-500/20 transition-transform group-hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">Pre-Test Avg</span>
                <p class="text-3xl font-black text-blue-400 leading-none mt-2 font-display italic tracking-tight">{{ number_format($preTestAvg, 1) }}</p>
            </div>
        </div>
        <p class="text-[9px] font-black text-text-tertiary/40 uppercase tracking-[0.4em] italic leading-none">Baseline Knowledge Index</p>
    </div>

    {{-- Post-Test Average --}}
    <div class="bg-surface p-8 rounded-4xl border border-divider shadow-premium hover:border-purple-500/30 transition-all group">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 bg-purple-500/10 text-purple-400 rounded-2xl flex items-center justify-center border border-purple-500/20 transition-transform group-hover:scale-110">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary">Post-Test Avg</span>
                <p class="text-3xl font-black text-purple-400 leading-none mt-2 font-display italic tracking-tight">{{ number_format($postTestAvg, 1) }}</p>
            </div>
        </div>
        @php $improvement = $preTestAvg > 0 ? (($postTestAvg - $preTestAvg) / max($preTestAvg, 1)) * 100 : 0; @endphp
        <div class="flex items-center gap-2 text-[10px] font-black {{ $improvement >= 0 ? 'text-emerald-400 bg-emerald-500/5 border-emerald-500/10' : 'text-red-400 bg-red-500/5 border-red-500/10' }} border px-3 py-1.5 rounded-full w-fit uppercase tracking-widest">
            @if($improvement >= 0)
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            @else
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
            @endif
            {{ $improvement >= 0 ? '+' : '' }}{{ number_format($improvement, 1) }}% Improvement
        </div>
    </div>
</div>

{{-- Main Report Table --}}
<div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
    {{-- Header with Filters --}}
    <div class="p-8 border-b border-divider bg-surface-light/30">
        <div class="flex flex-wrap items-center justify-between gap-6 mb-6">
            <div>
                <h3 class="font-black text-white text-xl tracking-tight">Assessment Results Ledger</h3>
                <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">Comprehensive evaluation records across all training modules</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reporting.assessments.export-csv', request()->query()) }}" class="flex items-center gap-2 px-5 py-2.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-500/20 hover:scale-105 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export XLSX
                </a>
                <a href="{{ route('admin.reporting.assessments.export-pdf', request()->query()) }}" target="_blank" class="flex items-center gap-2 px-5 py-2.5 bg-red-500/10 text-red-400 border border-red-500/20 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-500/20 hover:scale-105 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Export PDF
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form action="{{ route('admin.reporting.assessments') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..." class="px-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none shadow-sm placeholder:text-text-tertiary/50 min-w-[200px]">

            <select name="module_id" class="px-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-primary outline-none appearance-none cursor-pointer">
                <option value="">Semua Modul</option>
                @foreach($modules as $m)
                <option value="{{ $m->id }}" {{ request('module_id') == $m->id ? 'selected' : '' }}>{{ Str::limit($m->title, 30) }}</option>
                @endforeach
            </select>

            <select name="type" class="px-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-primary outline-none appearance-none cursor-pointer">
                <option value="">Semua Tipe</option>
                <option value="pretest" {{ request('type') == 'pretest' ? 'selected' : '' }}>Pre-Test</option>
                <option value="posttest" {{ request('type') == 'posttest' ? 'selected' : '' }}>Post-Test</option>
            </select>

            <select name="status" class="px-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-primary outline-none appearance-none cursor-pointer">
                <option value="">Semua Status</option>
                <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>Lulus</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Tidak Lulus</option>
            </select>

            <button type="submit" class="px-6 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-cyan-glow">
                Filter
            </button>
            @if(request()->hasAny(['search', 'module_id', 'type', 'status']))
            <a href="{{ route('admin.reporting.assessments') }}" class="px-5 py-3 bg-surface-light text-text-secondary border border-divider rounded-2xl text-[10px] font-black uppercase tracking-widest hover:text-white hover:border-primary/30 transition-all">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                <tr>
                    <th class="px-8 py-5">#</th>
                    <th class="px-6 py-5 min-w-[220px]">Peserta</th>
                    <th class="px-6 py-5">Training Module</th>
                    <th class="px-6 py-5 text-center">Tipe</th>
                    <th class="px-6 py-5 text-center">Skor</th>
                    <th class="px-6 py-5 text-center">Hasil</th>
                    <th class="px-6 py-5 text-center">Durasi</th>
                    <th class="px-6 py-5 text-right">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-divider/50">
                @forelse($attempts as $i => $attempt)
                @php
                    $duration = ($attempt->started_at && $attempt->completed_at)
                        ? $attempt->started_at->diff($attempt->completed_at)
                        : null;
                    $durationStr = $duration ? $duration->format('%im %ss') : '—';
                    $isPretest = ($attempt->assessment->type->value ?? '') === 'pretest';
                    $typeLabel = $isPretest ? 'Pre-Test' : 'Post-Test';
                @endphp
                <tr class="hover:bg-primary/5 transition-all duration-300 group">
                    <td class="px-8 py-5 text-text-tertiary text-[10px] font-mono">{{ str_pad($attempts->firstItem() + $i, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-sm font-black border border-primary/20 group-hover:scale-110 transition-transform">
                                {{ strtoupper(substr($attempt->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white group-hover:text-primary transition-colors">{{ $attempt->user->name ?? '—' }}</p>
                                <p class="text-[10px] text-text-tertiary font-mono opacity-50">{{ $attempt->user->email ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <p class="text-xs font-bold text-text-secondary">{{ $attempt->assessment->trainingModule->title ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-5 text-center">
                        <span class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $isPretest ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20' }}">
                            {{ $typeLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-5 text-center">
                        <div class="inline-flex flex-col items-center">
                            <span class="text-xl font-black font-display italic tracking-tight {{ $attempt->score >= 70 ? 'text-emerald-400' : ($attempt->score >= 40 ? 'text-yellow-400' : 'text-red-400') }}">
                                {{ $attempt->score ?? 0 }}
                            </span>
                            <div class="w-12 bg-surface-light h-1 rounded-full overflow-hidden border border-divider mt-1">
                                <div class="h-full rounded-full {{ $attempt->score >= 70 ? 'bg-emerald-400' : ($attempt->score >= 40 ? 'bg-yellow-400' : 'bg-red-400') }}" @style(['width' => ($attempt->score ?? 0) . '%'])></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-center">
                        @if($attempt->passed)
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[9px] font-black uppercase tracking-[0.2em]">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            Lulus
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 text-[9px] font-black uppercase tracking-[0.2em]">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            Gagal
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-5 text-center text-[10px] font-black text-text-tertiary uppercase tracking-widest opacity-60">{{ $durationStr }}</td>
                    <td class="px-6 py-5 text-right">
                        <p class="text-[10px] font-bold text-text-secondary">{{ $attempt->completed_at?->format('d M Y') }}</p>
                        <p class="text-[9px] font-mono text-text-tertiary opacity-50">{{ $attempt->completed_at?->format('H:i:s') }}</p>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-10 py-20 text-center">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-16 h-16 bg-surface-light rounded-2xl border border-divider flex items-center justify-center">
                                <svg class="w-8 h-8 text-text-tertiary opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <p class="text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic text-xs">No Assessment Records Detected.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attempts->hasPages())
    <div class="px-8 py-6 bg-surface-light/30 border-t border-divider">
        {{ $attempts->links() }}
    </div>
    @endif
</div>
@endsection
