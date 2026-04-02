@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.vr-devices.index') }}" class="hover:text-primary transition-colors">VR Fleet Grid</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Node Identity</span>
    </div>
    <div class="flex items-center justify-between mt-1">
        <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">{{ $vrDevice->device_name ?? 'Undefined Nexus' }}</h1>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-8">
    
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Hardware Profile Form -->
        <div class="lg:col-span-3 space-y-8">
            <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 relative overflow-hidden group">
                <div class="absolute -right-32 -top-32 w-64 h-64 bg-primary/5 rounded-full blur-[100px] pointer-events-none group-hover:bg-primary/10 transition-colors duration-700"></div>
                <div class="relative z-10">
                    <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display border-b border-divider pb-4 mb-8">Hardware Configuration Matrix</h3>
                    
                    <form action="{{ route('admin.vr-devices.update', $vrDevice->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">Display Name</label>
                                <input type="text" name="device_name" value="{{ old('device_name', $vrDevice->device_name) }}"
                                    class="w-full px-5 py-4 bg-background border border-divider rounded-2xl text-sm font-bold text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all shadow-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">Operational State</label>
                                <div class="relative">
                                    <select name="status" class="w-full px-5 py-4 bg-background border border-divider rounded-2xl text-sm font-bold text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all shadow-sm appearance-none cursor-pointer">
                                        <option value="active" {{ $vrDevice->status === 'active' ? 'selected' : '' }}>ONLINE (Active)</option>
                                        <option value="inactive" {{ $vrDevice->status === 'inactive' ? 'selected' : '' }}>OFFLINE (Standby)</option>
                                        <option value="maintenance" {{ $vrDevice->status === 'maintenance' ? 'selected' : '' }}>MAINTENANCE (Hold)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-primary">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                            <div>
                                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">Unique Identifier (Read-Only)</label>
                                <div class="w-full px-5 py-4 bg-surface-light border border-divider rounded-2xl text-xs font-mono font-bold text-text-secondary opacity-60 flex items-center shadow-inner cursor-not-allowed">
                                    <svg class="w-4 h-4 mr-3 text-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    <span class="truncate">{{ $vrDevice->headset_identifier }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">Hardware Category</label>
                                <div class="w-full px-5 py-4 bg-surface-light border border-divider rounded-2xl text-xs font-mono font-bold text-text-secondary opacity-60 flex items-center shadow-inner cursor-not-allowed uppercase">
                                    {{ $vrDevice->device_type ?? 'UNKNOWN HMD' }}
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-divider/50 mt-8 flex justify-end gap-3">
                            <button type="submit" class="px-8 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-cyan-glow hover:scale-[1.02] transition-all">
                                Commit Sync
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Sessions -->
            <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
                <div class="p-8 border-b border-divider bg-surface-light/30">
                    <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display">Recent Operator Logs</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                            <tr>
                                <th class="px-8 py-5">Session Matrix</th>
                                <th class="px-8 py-5 text-center">Outcome</th>
                                <th class="px-8 py-5 text-right w-32">Data Sync</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-divider/50">
                            @forelse($recentSessions as $session)
                            <tr class="hover:bg-primary/5 transition-colors duration-300">
                                <td class="px-8 py-4">
                                    <div class="text-sm font-bold text-white mb-0.5">{{ $session->trainingModule ? $session->trainingModule->title : 'Unknown Cluster' }}</div>
                                    <div class="text-[9px] text-text-tertiary uppercase tracking-widest font-mono">{{ $session->started_at->format('d M Y, H:i') }}</div>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    @php
                                        $style = match($session->session_status) {
                                            'completed' => 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
                                            'failed', 'interrupted' => 'text-red-400 bg-red-500/10 border-red-500/20',
                                            default => 'text-primary bg-primary/10 border-primary/20'
                                        };
                                    @endphp
                                    <span class="px-3 py-1 border {{ $style }} rounded-full text-[9px] font-black uppercase tracking-widest inline-flex items-center">{{ $session->session_status }}</span>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <a href="{{ route('admin.monitoring.vr.detail', $session->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-surface-light border border-divider hover:border-primary/50 text-text-tertiary hover:text-primary transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-8 py-10 text-center text-xs font-bold text-text-tertiary italic uppercase tracking-widest">
                                    No telemetry recorded for this node
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Stats Bar -->
        <div class="space-y-6">
            <div class="bg-surface rounded-4xl border border-divider shadow-premium p-6 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em]">Last Uplink Ping</div>
                        <div class="text-sm font-bold text-white">{{ $vrDevice->last_seen_at ? $vrDevice->last_seen_at->diffForHumans() : 'Never' }}</div>
                    </div>
                </div>

                <div class="h-px bg-divider w-full my-2"></div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em]">Software Payload</div>
                        <div class="text-sm font-bold text-white font-mono uppercase">VER_{{ $vrDevice->app_version ?? '0.0' }}</div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-500/5 rounded-4xl border border-red-500/20 shadow-premium p-6 mt-10">
                <h3 class="text-[10px] font-black text-red-500 uppercase tracking-[0.3em] mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Critical Node Override
                </h3>
                <p class="text-[11px] text-text-secondary mb-6 leading-relaxed">
                    Retiring this node will permanently sever it from active operational deployment grids. Data preservation protocols will maintain historical operational logs.
                </p>
                <form action="{{ route('admin.vr-devices.destroy', $vrDevice->id) }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to retire this hardware node? This action is disruptive to active fleets.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-5 py-3 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white border border-red-500/30 font-black text-[10px] uppercase tracking-widest rounded-2xl transition-all shadow-sm">
                        Decommission Node
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
