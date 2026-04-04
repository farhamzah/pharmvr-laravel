@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.users.index') }}" class="hover:text-primary transition-colors">Assets</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Provision New Asset</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Asset Provisioning</h1>
</div>
@endsection

@section('content')
<div class="max-w-6xl relative pb-20">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <!-- Horizontal Header Section -->
        <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-primary/10 mb-8">
            <div class="flex flex-col md:flex-row items-center gap-8 p-8 bg-surface-light/20">
                <div class="w-16 h-16 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-2xl font-black text-white tracking-tight uppercase italicLine">Create New User</h3>
                    <p class="text-[10px] text-text-tertiary font-black uppercase tracking-widest mt-1 opacity-60 italic">Add a new member to the PharmVR ecosystem.</p>
                </div>
                <div class="flex items-center gap-4">
                    <button type="button" onclick="window.history.back()" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] hover:text-white transition-colors italic">Cancel</button>
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-background px-8 py-4 rounded-xl text-[10px] font-black uppercase tracking-[0.3em] transition-all shadow-cyan-glow">
                        Create User
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Primary & Institutional -->
            <div class="lg:col-span-2 space-y-8">
                <!-- User Information Section -->
                <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-divider/50">
                        <h4 class="text-[11px] font-black text-white uppercase tracking-[0.4em] italic">User Information</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label for="name" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all @error('name') border-red-500 @enderror"
                                placeholder="e.g. John Doe">
                            @error('name') <p class="text-[9px] font-bold text-red-500 mt-1 uppercase italic tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all @error('email') border-red-500 @enderror"
                                placeholder="user@unpad.ac.id">
                            @error('email') <p class="text-[9px] font-bold text-red-500 mt-1 uppercase italic tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="role" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Access Role</label>
                            <div class="relative">
                                <select id="role" name="role" class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all appearance-none cursor-pointer">
                                    <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                                    <option value="instructor" {{ old('role') === 'instructor' ? 'selected' : '' }}>Instructor</option>
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="status" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Account Status</label>
                            <div class="relative">
                                <select id="status" name="status" class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all appearance-none cursor-pointer">
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Details Section -->
                <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-divider/50">
                        <h4 class="text-[11px] font-black text-white uppercase tracking-[0.4em] italic">Academic Details</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label for="university" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">University</label>
                            <input type="text" id="university" name="university" value="{{ old('university') }}" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all"
                                placeholder="e.g. University of Padjadjaran">
                        </div>

                        <div class="space-y-2">
                            <label for="nim" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Student ID (NIM)</label>
                            <input type="text" id="nim" name="nim" value="{{ old('nim') }}" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all"
                                placeholder="Enter NIM">
                        </div>

                        <div class="space-y-2">
                            <label for="semester" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Semester</label>
                            <input type="number" id="semester" name="semester" value="{{ old('semester') }}" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all"
                                placeholder="1">
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all"
                                placeholder="+62 ...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Security & Credentials -->
            <div class="space-y-8">
                <!-- Access Credentials Section -->
                <div class="bg-surface rounded-4xl border border-divider shadow-premium p-8">
                    <div class="flex items-center gap-3 mb-8 pb-4 border-b border-divider/50">
                        <h4 class="text-[11px] font-black text-white uppercase tracking-[0.4em] italic">Access Credentials</h4>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="password" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Initial Password</label>
                            <input type="password" id="password" name="password" required placeholder="••••••••" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all placeholder:text-text-tertiary/20">
                            @error('password') <p class="text-[9px] font-bold text-red-500 mt-1 uppercase italic tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="password_confirmation" class="text-[10px] font-black text-text-tertiary uppercase tracking-widest">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" 
                                class="w-full px-5 py-3.5 bg-surface-light border border-divider rounded-xl text-white font-bold focus:border-primary outline-none transition-all placeholder:text-text-tertiary/20">
                        </div>
                    </div>
                </div>

                <!-- Admin Guide Panel -->
                <div class="bg-surface rounded-3xl border border-divider shadow-premium p-6 border-dashed opacity-80">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-[10px] font-black text-white uppercase tracking-widest">Admin Guide</span>
                    </div>
                    <div class="space-y-4">
                        <p class="text-[9px] font-bold text-text-tertiary uppercase tracking-widest leading-relaxed italic">
                            • All fields except phone and university are required for a complete profile.
                        </p>
                        <p class="text-[9px] font-bold text-text-tertiary uppercase tracking-widest leading-relaxed italic">
                            • Users will receive an activation email once creation is confirmed.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
