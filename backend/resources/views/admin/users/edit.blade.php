@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.users.index') }}" class="hover:text-primary transition-colors">Assets</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Modify Profile</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Asset Configuration</h1>
</div>
@endsection

@section('content')
<div class="max-w-6xl relative pb-20">
    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Compact Header Profile Section -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-primary/10 mb-8">
            <div class="flex flex-col md:flex-row items-center gap-8 p-8 bg-surface-light/20">
                <div class="w-24 h-24 rounded-3xl bg-primary text-background flex items-center justify-center text-3xl font-black shadow-cyan-glow relative overflow-hidden group shrink-0">
                    @if($user->profile?->avatar_url)
                        <img src="{{ Storage::url($user->profile->avatar_url) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                        <h3 class="text-2xl font-black text-white tracking-tight uppercase italic">{{ $user->name }}</h3>
                        <span class="px-3 py-1 {{ $user->isSuperAdmin() ? 'bg-primary text-background' : 'bg-primary/10 text-primary' }} border border-primary/20 rounded-lg text-[10px] font-black uppercase tracking-widest italic flex items-center justify-center self-center md:self-auto">
                            {{ str_replace('_', ' ', $user->role) }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-6 gap-y-2 mt-3 text-[10px] font-black uppercase tracking-widest text-text-tertiary">
                        <div class="flex items-center gap-2">
                            <span class="opacity-40 italic">ID:</span>
                            <span class="text-white">UID_{{ strtoupper(dechex($user->id)) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="opacity-40 italic">EMAIL:</span>
                            <span class="text-white">{{ $user->email }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="opacity-40 italic">SYNC:</span>
                            <span class="text-white">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button type="button" onclick="window.history.back()" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] hover:text-white transition-colors italic">Discard</button>
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-background px-8 py-4 rounded-xl text-[10px] font-black uppercase tracking-[0.3em] transition-all shadow-cyan-glow">
                        Update Asset
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Primary & Institutional -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Basic Information Section -->
                <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-divider/50">
                        <h4 class="text-[11px] font-black text-white uppercase tracking-[0.4em] italic">Basic Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label for="name" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all @error('name') border-red-500 @enderror">
                            @error('name') <p class="text-[9px] font-bold text-red-500 mt-1 uppercase italic tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="role" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Access Role</label>
                            <div class="relative">
                                <select id="role" name="role" {{ $user->isSuperAdmin() && !auth()->user()->isSuperAdmin() ? 'disabled' : '' }} class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all appearance-none cursor-pointer {{ $user->isSuperAdmin() ? 'opacity-50' : '' }}">
                                    <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student</option>
                                    <option value="instructor" {{ old('role', $user->role) === 'instructor' ? 'selected' : '' }}>Instructor</option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                                    <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            @if($user->isSuperAdmin())
                                <p class="text-[8px] font-bold text-primary mt-1 uppercase italic tracking-widest opacity-60">Super Admin privileges require direct root authorization.</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="status" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Account Status</label>
                            <div class="relative">
                                <select id="status" name="status" {{ $user->role === 'super_admin' && !auth()->user()->isSuperAdmin() ? 'disabled' : '' }} class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all appearance-none cursor-pointer {{ $user->role === 'super_admin' ? 'opacity-50' : '' }}">
                                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status', $user->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Institutional Sync Section -->
                <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-divider/50">
                        <h4 class="text-[11px] font-black text-white uppercase tracking-[0.4em] italic">Institutional Sync</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label for="university" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Academic Institution</label>
                            <input type="text" id="university" name="university" value="{{ old('university', $user->profile?->university) }}" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all"
                                placeholder="e.g. UNIVERSITY OF PADJADJARAN">
                        </div>

                        <div class="space-y-2">
                            <label for="nim" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Student ID (NIM)</label>
                            <input type="text" id="nim" name="nim" value="{{ old('nim', $user->profile?->nim) }}" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all"
                                placeholder="...">
                        </div>

                        <div class="space-y-2">
                            <label for="semester" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Current Semester</label>
                            <input type="number" id="semester" name="semester" value="{{ old('semester', $user->profile?->semester) }}" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Contact Number</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->profile?->phone) }}" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Security & Credentials -->
            <div class="space-y-8">
                <!-- Security & Credentials -->
                <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-divider/50">
                        <h4 class="text-[11px] font-black text-white uppercase tracking-[0.4em] italic">Security</h4>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="avatar" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Profile Avatar</label>
                            <div class="mt-1">
                                <input type="file" id="avatar" name="avatar" class="block w-full text-[10px] text-text-tertiary italic
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-lg file:border-0
                                    file:text-[10px] file:font-black file:uppercase
                                    file:bg-primary/10 file:text-primary
                                    hover:file:bg-primary/20 transition-all">
                            </div>
                            @error('avatar') <p class="text-[9px] font-bold text-red-500 mt-1 uppercase italic tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2 pt-4 border-t border-divider/30">
                            <label for="password" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Update Password</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all placeholder:text-text-tertiary/20">
                            @error('password') <p class="text-[9px] font-bold text-red-500 mt-1 uppercase italic tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="password_confirmation" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Verify Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all placeholder:text-text-tertiary/20">
                        </div>
                    </div>
                </div>

                <!-- Status Metadata -->
                <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8 border-dashed opacity-80">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                            <span class="text-text-tertiary italic">Verification Status</span>
                            <span class="text-primary">Verified Asset</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                            <span class="text-text-tertiary italic">Last Protocol Sync</span>
                            <span class="text-white">Active Now</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone: Improved & Contained -->
        <div class="mt-12 bg-surface rounded-4xl border border-red-500/20 shadow-premium overflow-hidden">
            <div class="px-8 py-5 bg-red-500/5 border-b border-red-500/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div>
                    <h4 class="text-[10px] font-black text-red-500 uppercase tracking-[0.4em] italic">Critical Override Protocols</h4>
                </div>
                <div class="text-[8px] font-black text-red-500/50 uppercase tracking-widest">Authorized Personnel Only</div>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-start">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-5 bg-surface-light/30 border border-divider rounded-2xl hover:border-red-500/20 transition-all group">
                            <div>
                                <p class="text-[10px] font-black text-white uppercase tracking-tight">Protocol Purge</p>
                                <p class="text-[8px] text-text-tertiary mt-0.5 italic">Kill all active terminal links.</p>
                            </div>
                            <button type="button" 
                                {{ $user->isSuperAdmin() && !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}
                                onclick="if(confirm('Initiate local session purge?')) { document.getElementById('force-logout-form').submit(); }"
                                class="px-4 py-1.5 bg-red-500/10 text-red-500 text-[8px] font-black rounded-lg border border-red-500/20 hover:bg-red-500 hover:text-white transition-all uppercase tracking-widest disabled:opacity-30">
                                Purge
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="p-6 bg-surface-light/10 border border-divider border-dashed rounded-2xl">
                            <p class="text-[9px] font-bold text-text-tertiary uppercase tracking-[0.2em] italic mb-2">Security Advisory</p>
                            <p class="text-[10px] text-text-tertiary leading-relaxed">Account status modifications and session purges are logged in the audit trail. Unauthorized modifications of elevated assets (Super Admin) are strictly prohibited via standard administrative interfaces.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="force-logout-form" action="{{ route('admin.users.force-logout', $user) }}" method="POST" class="hidden">
        @csrf
    </form>
</div>

        <form id="force-logout-form" action="{{ route('admin.users.force-logout', $user) }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</div>
@endsection
