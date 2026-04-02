@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Assets</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">User Management</h1>
</div>
@endsection

@section('content')
<div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
    <!-- Action Bar -->
    <div class="p-8 border-b border-divider bg-surface-light/30 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex-1 max-w-2xl flex items-center gap-4">
            <div class="relative flex-1 group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-text-tertiary group-focus-within:text-primary transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or user ID..." 
                    class="w-full pl-12 pr-6 py-4 bg-surface-light border border-divider rounded-2xl text-sm text-white focus:border-primary focus:bg-surface-light outline-none transition-all placeholder-text-tertiary/30">
            </div>
            
            <div class="relative min-w-[160px]">
                <select name="role" onchange="this.form.submit()" 
                    class="w-full bg-surface-light border border-divider rounded-2xl px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-secondary outline-none appearance-none hover:border-primary/50 transition-all cursor-pointer">
                    <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>All Roles</option>
                    <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Instructor</option>
                    <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary/50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
            
            @if(request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="text-[10px] font-black text-text-tertiary hover:text-primary uppercase tracking-widest transition-colors">Reset</a>
            @endif
        </form>
        
        <a href="{{ route('admin.users.create') }}" class="bg-primary hover:bg-primary-dark text-background px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-cyan-glow flex items-center gap-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            Provision New User
        </a>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                <tr>
                    <th class="px-10 py-5">User Details</th>
                    <th class="px-10 py-5">Role</th>
                    <th class="px-10 py-5">Account Status</th>
                    <th class="px-10 py-5">Registration Date</th>
                    <th class="px-10 py-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-divider/50">
                @forelse($users as $user)
                <tr class="hover:bg-primary/5 transition-all duration-300 group">
                    <td class="px-10 py-6">
                        <div class="flex items-center gap-5">
                            <div class="relative w-12 h-12 flex-shrink-0">
                                <div class="w-full h-full rounded-full ring-2 ring-divider group-hover:ring-primary/30 transition-all overflow-hidden relative">
                                    <div class="w-full h-full bg-primary text-background flex items-center justify-center text-sm font-black">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    @if($user->profile && $user->profile->avatar_url)
                                        <img src="{{ Storage::url($user->profile->avatar_url) }}" class="absolute inset-0 w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-surface {{ $user->is_banned ? 'bg-red-500' : 'bg-emerald-500' }} z-10"></div>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white group-hover:text-primary transition-colors tracking-tight">{{ $user->name }}</div>
                                <div class="text-[10px] text-text-tertiary font-medium opacity-60">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-10 py-6">
                        @php
                            $roleStyles = [
                                'super_admin' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                'admin' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                                'instructor' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                'student' => 'bg-surface-light text-text-tertiary border-divider',
                            ];
                            $currentRoleStyle = $roleStyles[$user->role] ?? $roleStyles['student'];
                        @endphp
                        <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-[0.15em] w-fit border {{ $currentRoleStyle }}">
                            {{ str_replace('_', ' ', $user->role) }}
                        </span>
                    </td>
                    <td class="px-10 py-6">
                        <div class="flex items-center gap-2">
                            @php
                                $statusStyles = [
                                    'active' => 'bg-emerald-500 text-background border-emerald-400 shadow-emerald-glow/20',
                                    'pending' => 'bg-orange-500 text-background border-orange-400 shadow-orange-glow/20',
                                    'suspended' => 'bg-red-500 text-background border-red-400 shadow-red-glow/20',
                                    'inactive' => 'bg-surface-light text-text-tertiary border-divider opacity-40'
                                ];
                                $currentStatusStyle = $statusStyles[$user->status] ?? $statusStyles['inactive'];
                            @endphp
                            <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-[0.15em] w-fit border shadow-sm {{ $currentStatusStyle }}">
                                {{ $user->status }}
                            </span>
                        </div>
                    </td>
                    <td class="px-10 py-6 text-[10px] font-bold text-text-tertiary uppercase tracking-widest opacity-60">
                        {{ $user->created_at->format('d M / Y') }}
                    </td>
                    <td class="px-10 py-6 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.users.edit', $user) }}" class="p-3 text-text-tertiary hover:text-primary bg-surface-light/50 hover:bg-surface-light border border-divider rounded-xl transition-all" title="Edit User">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                            
                            <form action="{{ route('admin.users.force-logout', $user) }}" method="POST" onsubmit="return confirm('Initiate session termination for this user?')" class="inline">
                                @csrf
                                <button type="submit" class="p-3 text-text-tertiary hover:text-orange-400 bg-surface-light/50 hover:bg-surface-light border border-divider rounded-xl transition-all" title="End Session">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>

                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Permanent deletion of user and all related data. Proceed?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-3 text-red-500/40 hover:text-red-500 bg-red-500/5 hover:bg-red-500/10 border border-red-500/10 rounded-xl transition-all" title="Delete User">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-10 py-20 text-center text-text-tertiary font-bold uppercase tracking-[0.4em] opacity-30 italic">No User Matches Found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="px-10 py-6 border-t border-divider bg-surface-light/30">
        <div class="pagination-premium">
            {{ $users->links() }}
        </div>
    </div>
    @endif
</div>

<style>
    /* Pagination Overrides for Premium Theme */
    .pagination-premium nav { display: flex; justify-content: center; }
    .pagination-premium a, .pagination-premium span { 
        background: #1C2733 !important; 
        border: 1px solid #2A3545 !important; 
        color: #B0BEC5 !important; 
        border-radius: 12px !important;
        margin: 0 4px !important;
        font-weight: 800 !important;
        font-size: 10px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        transition: all 0.3s !important;
    }
    .pagination-premium a:hover { 
        border-color: #00E5FF !important; 
        color: #00E5FF !important; 
        box-shadow: 0 0 10px rgba(0, 229, 255, 0.2);
    }
    .pagination-premium .active span {
        background: #00E5FF !important;
        border-color: #00E5FF !important;
        color: #0A0F14 !important;
        box-shadow: 0 0 15px rgba(0, 229, 255, 0.3);
    }
</style>
@endsection
