@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Command Center</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Hardware Ops</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">VR Fleet Management</h1>
</div>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Filter -->
    <div class="bg-surface rounded-3xl border border-divider shadow-premium p-6 flex flex-wrap gap-4 items-end justify-between">
        <form method="GET" class="flex flex-wrap gap-4 items-end flex-1">
            <div class="flex-1 max-w-sm">
                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">Node Locator</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Device ID, Name, OS Ver..." 
                        class="w-full pl-10 pr-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white placeholder-text-tertiary focus:ring-2 focus:ring-primary focus:border-transparent outline-none shadow-sm transition-all focus:bg-surface duration-300">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2">Node Status</label>
                <select name="status" class="px-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none shadow-sm min-w-[200px]">
                    <option value="">All States</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>ONLINE (Active)</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>OFFLINE (Standby)</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>MAINTENANCE</option>
                    <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>RETIRED</option>
                </select>
            </div>
            <button type="submit" class="px-8 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-cyan-glow hover:scale-105 transition-all">
                Execute Scan
            </button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.vr-devices.index') }}" class="px-8 py-3 text-red-500 hover:bg-red-500/10 border border-transparent hover:border-red-500/20 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">Abort Scan</a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden relative group">
        <div class="absolute -right-20 -top-20 w-48 h-48 bg-primary/5 rounded-full blur-[80px] pointer-events-none group-hover:bg-primary/10 transition-colors"></div>
        <div class="p-8 border-b border-divider bg-surface-light/30 flex items-center justify-between">
            <h3 class="font-black text-white text-lg tracking-tight uppercase italic font-display">Global Node Matrix</h3>
            <div class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">{{ $devices->total() }} total nodes</div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                    <tr>
                        <th class="px-8 py-5">Hardware Signature</th>
                        <th class="px-8 py-5">Platform & Version</th>
                        <th class="px-8 py-5">Last Synced Operator</th>
                        <th class="px-8 py-5 text-center">Node State</th>
                        <th class="px-8 py-5">Last Broadcast</th>
                        <th class="px-8 py-5 text-right w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/50 relative z-10">
                    @forelse($devices as $device)
                    <tr class="hover:bg-primary/5 transition-colors duration-300">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-surface-light border border-divider flex items-center justify-center text-text-tertiary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"></path><rect x="4" y="8" width="16" height="8" rx="2" stroke-width="1.5"></rect></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white mb-0.5">{{ $device->device_name ?? 'Undefined Nexus' }}</div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full {{ $device->status === 'active' ? 'bg-primary' : 'bg-text-tertiary' }}"></div>
                                        <div class="text-[9px] text-text-tertiary font-mono uppercase tracking-widest break-all max-w-[150px] truncate" title="{{ $device->headset_identifier }}">{{ $device->headset_identifier }}</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <span class="text-xs font-bold text-white tracking-wider flex items-center gap-2">
                                {{ strtoupper($device->platform_name ?? 'UNKNOWN OS') }}
                            </span>
                            <div class="text-[9px] text-primary font-mono uppercase tracking-[0.2em] opacity-80 mt-1">VER_{{ $device->app_version ?? '0.0.0' }}</div>
                        </td>
                        <td class="px-8 py-5">
                            @if($device->user)
                                <div class="text-xs font-bold text-text-secondary">{{ $device->user->name }}</div>
                            @else
                                <span class="text-xs font-medium text-text-tertiary italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-8 py-5 text-center">
                            @php
                                $statusStyle = match($device->status) {
                                    'active' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'inactive' => 'bg-surface-light text-text-tertiary border-divider',
                                    'maintenance' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                    'retired' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                    default => 'bg-surface-light text-text-tertiary border-divider'
                                };
                            @endphp
                            <span class="px-3 py-1 {!! $statusStyle !!} border rounded-full text-[9px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm">
                                @if($device->status === 'active') <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> @endif
                                {{ $device->status }}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="text-xs font-bold text-white group-hover:text-primary transition-colors">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}</div>
                            <div class="text-[9px] text-text-tertiary uppercase tracking-widest mt-1">{{ $device->last_seen_at ? $device->last_seen_at->format('M d, H:i') : '--' }}</div>
                        </td>
                        <td class="px-8 py-5 text-right w-32">
                            <a href="{{ route('admin.vr-devices.show', $device->id) }}" class="inline-flex items-center justify-center w-10 h-10 bg-surface-light border border-divider hover:border-primary/50 text-text-tertiary hover:text-primary rounded-2xl transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-20 text-center">
                            <div class="w-16 h-16 rounded-full bg-surface-light/50 border border-divider mx-auto border-dashed flex items-center justify-center text-text-tertiary mb-4">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-text-secondary font-display uppercase italic tracking-widest mb-1">No Hardware Nodes Found</p>
                            <p class="text-xs text-text-tertiary">Adjust scan parameters or synchronize new devices</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
        <div class="p-6 border-t border-divider bg-surface-light/10">
            {{ $devices->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>
@endsection
