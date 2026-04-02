@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">System Logs</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Administrative Audit Trail</h1>
</div>
@endsection

@section('content')
<div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-primary/10">
    <div class="p-10 border-b border-divider bg-surface-light/30 flex items-center justify-between">
        <div>
            <h3 class="font-black text-white text-lg tracking-tight uppercase italic underline decoration-primary/20 decoration-2 underline-offset-8">Master Ledger</h3>
            <p class="text-[10px] text-text-tertiary font-black uppercase tracking-[0.2em] mt-3 opacity-60">Tracking all administrative transactions...</p>
        </div>
        <div class="flex flex-col items-end gap-1">
            <span class="text-[10px] font-black text-primary uppercase tracking-[0.3em] font-mono italic">SECURE_AUTH_LAYER</span>
            <span class="text-[9px] text-text-tertiary uppercase tracking-widest opacity-40">Verifying Integrity...</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                <tr>
                    <th class="px-10 py-5">Entropy Timestamp</th>
                    <th class="px-10 py-5">Origin Agent</th>
                    <th class="px-10 py-5">Executed Protocol</th>
                    <th class="px-10 py-5">Target Resource</th>
                    <th class="px-10 py-5">Node IP</th>
                    <th class="px-10 py-5 text-right">Analysis</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-divider/50">
                @forelse($logs as $log)
                <tr class="hover:bg-primary/5 transition-all duration-300 group">
                    <td class="px-10 py-6 text-[10px] font-bold text-text-tertiary font-mono opacity-60 uppercase tracking-tighter">
                        {{ strtoupper($log->created_at->format('d M / H:i:s.v')) }}
                    </td>
                    <td class="px-10 py-6">
                        @if($log->user)
                            <a href="{{ $log->user->role === 'student' ? route('admin.reporting.user-report', $log->user) : '#' }}" class="group/user">
                                <div class="text-sm font-bold text-white group-hover/user:text-primary transition-colors italic uppercase tracking-tight">{{ $log->user->name }}</div>
                                <div class="text-[9px] text-text-tertiary font-mono opacity-40 uppercase tracking-tighter">{{ strtoupper($log->user->email) }}</div>
                            </a>
                        @else
                            <div class="text-[10px] font-black text-primary/70 italic uppercase tracking-[0.2em]">KERNEL_SYSTEM</div>
                        @endif
                    </td>
                    <td class="px-10 py-6">
                        @php
                            $actionType = strtolower($log->action);
                            $badgeStyle = match(true) {
                                str_contains($actionType, 'created') => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                str_contains($actionType, 'updated') => 'bg-primary/10 text-primary border-primary/20 shadow-cyan-glow',
                                str_contains($actionType, 'deleted'), str_contains($actionType, 'banned'), str_contains($actionType, 'error') => 'bg-red-500/10 text-red-500 border-red-500/20',
                                default => 'bg-surface-light text-text-tertiary border-divider'
                            };
                        @endphp
                        <span class="inline-flex px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-[0.2em] border {{ $badgeStyle }} italic">
                            {{ strtoupper($log->action) }}
                        </span>
                    </td>
                    <td class="px-10 py-6">
                        <div class="text-xs font-black text-white group-hover:text-primary transition-colors uppercase tracking-widest">{{ class_basename($log->model_type) }}</div>
                        <div class="text-[9px] text-text-tertiary font-mono opacity-40 uppercase tracking-tighter">HEX: #{{ strtoupper(dechex($log->model_id)) }}</div>
                    </td>
                    <td class="px-10 py-6 text-[10px] font-bold font-mono text-text-tertiary opacity-40 italic">
                        {{ $log->ip_address }}
                    </td>
                    <td class="px-10 py-6 text-right">
                        <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-primary hover:text-white text-[9px] font-black uppercase tracking-[0.3em] underline decoration-primary/20 hover:decoration-primary transition-all underline-offset-4 italic">
                            Inspect
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-10 py-20 text-center text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic">Audit Ledger Empty.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="px-10 py-6 border-t border-divider bg-surface-light/30">
        <div class="pagination-premium">
            {{ $logs->links() }}
        </div>
    </div>
    @endif
</div>

<style>
    /* Regional overrides */
    .pagination-premium nav { display: flex; justify-content: center; }
</style>
@endsection
