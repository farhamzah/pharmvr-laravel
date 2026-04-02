@extends('layouts.admin')

@section('header', 'Telemetry Config')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex flex-col gap-1">
        <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
            <a href="{{ route('admin.assessments.index') }}" class="hover:text-primary transition-colors">Assessments</a>
            <span class="text-primary/30">/</span>
            <span class="text-white">Edit Assessment</span>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Modify Assessment</h1>
    </div>

    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
        <form action="{{ route('admin.assessments.update', $assessment) }}" method="POST" class="p-10">
            @csrf
            @method('PUT')

            <div class="space-y-8">
                <div class="space-y-3">
                    <label for="title" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Assessment Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $assessment->title) }}" required 
                        class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-sm text-white focus:border-primary focus:bg-surface-light outline-none transition-all placeholder-text-tertiary/30">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-3">
                        <label for="training_module_id" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Linked Training Module</label>
                        <div class="relative">
                            <select id="training_module_id" name="training_module_id" required 
                                class="w-full bg-surface-light border border-divider rounded-2xl px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-secondary outline-none appearance-none hover:border-primary/50 transition-all cursor-pointer">
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ $assessment->training_module_id == $module->id ? 'selected' : '' }}>{{ $module->title }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary/50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label for="type" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Assessment Type</label>
                        <div class="relative">
                            <select id="type" name="type" 
                                class="w-full bg-surface-light border border-divider rounded-2xl px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-secondary outline-none appearance-none hover:border-primary/50 transition-all cursor-pointer">
                                <option value="pre-test" {{ $assessment->type === 'pre-test' ? 'selected' : '' }}>Pre-Test</option>
                                <option value="post-test" {{ $assessment->type === 'post-test' ? 'selected' : '' }}>Post-Test</option>
                                <option value="practice" {{ $assessment->type === 'practice' ? 'selected' : '' }}>Practice</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary/50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-3">
                        <label for="min_score" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Passing Score (%)</label>
                        <input type="number" id="min_score" name="min_score" value="{{ old('min_score', $assessment->min_score) }}" min="0" max="100" required 
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-sm text-white focus:border-primary focus:bg-surface-light outline-none transition-all placeholder-text-tertiary/30">
                    </div>

                    <div class="space-y-3">
                        <label for="duration_minutes" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Time Limit (Minutes)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $assessment->duration_minutes) }}" min="1" required 
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-sm text-white focus:border-primary focus:bg-surface-light outline-none transition-all placeholder-text-tertiary/30">
                    </div>
                </div>

                <div class="p-6 bg-surface-light/50 border border-divider rounded-2xl flex items-center justify-between group">
                    <div>
                        <p class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] leading-none mb-2 opacity-60 italic">Active Status</p>
                        <p class="text-xs text-text-secondary font-bold">Currently {{ $assessment->is_active ? 'Active' : 'Draft' }}</p>
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $assessment->is_active) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-12 h-6 bg-divider rounded-full peer-focus:outline-none peer peer-checked:after:translate-x-full peer-checked:after:border-background after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-text-tertiary after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary peer-checked:after:bg-background"></div>
                    </label>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-divider flex items-center justify-end gap-6">
                <button type="button" onclick="window.history.back()" class="text-[10px] font-black text-text-tertiary hover:text-white uppercase tracking-widest transition-colors">Cancel</button>
                <button type="submit" class="bg-primary hover:bg-primary-dark text-background px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-cyan-glow">Update Assessment</button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="mt-16 bg-red-500/5 rounded-4xl border border-red-500/10 shadow-premium overflow-hidden">
        <div class="px-10 py-5 bg-red-500/10 border-b border-red-500/10">
            <h3 class="text-[10px] font-black text-red-500 uppercase tracking-[0.3em] italic">Danger Zone</h3>
        </div>
        <div class="p-10 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="text-center md:text-left">
                <h4 class="text-sm font-bold text-white mb-2 uppercase tracking-wide">Delete Assessment</h4>
                <p class="text-[11px] text-text-tertiary leading-relaxed max-w-xl">Permanently remove this assessment bank. Student attempts and historical data will be disconnected. Irreversible.</p>
            </div>
            <form action="{{ route('admin.assessments.destroy', $assessment) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this assessment?');" class="flex-shrink-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-8 py-4 bg-red-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-red-600 transition-all shadow-lg shadow-red-500/20">
                    Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
