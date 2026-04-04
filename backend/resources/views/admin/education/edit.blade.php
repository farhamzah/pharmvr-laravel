@extends('layouts.admin')

@section('header', 'Module Configuration')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex flex-col gap-1">
        <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
            <a href="{{ route('admin.education.index') }}" class="hover:text-primary transition-colors">Education</a>
            <span class="text-primary/30">/</span>
            <span class="text-white">Edit Module</span>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Modify Training Module</h1>
    </div>

    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
        <form action="{{ route('admin.education.update', $module) }}" method="POST" enctype="multipart/form-data" class="p-10">
            @csrf
            @method('PUT')

            <div class="space-y-8">
                <div class="space-y-3">
                    <label for="title" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Module Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $module->title) }}" required 
                        class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-sm text-white focus:border-primary focus:bg-surface-light outline-none transition-all placeholder-text-tertiary/30 @error('title') border-red-500 @enderror">
                    @error('title') <p class="text-[10px] font-bold text-red-500 mt-2 uppercase tracking-widest">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3">
                    <label for="description" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Description</label>
                    <textarea id="description" name="description" rows="4" required 
                        class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-sm text-white focus:border-primary focus:bg-surface-light outline-none transition-all placeholder-text-tertiary/30">{{ old('description', $module->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-3">
                        <label for="difficulty" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Difficulty Level</label>
                        <div class="relative">
                            <select id="difficulty" name="difficulty" class="w-full bg-surface-light border border-divider rounded-2xl px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-secondary outline-none appearance-none hover:border-primary/50 transition-all cursor-pointer">
                                <option value="Beginner" {{ old('difficulty', $module->difficulty) === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="Intermediate" {{ old('difficulty', $module->difficulty) === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="Advanced" {{ old('difficulty', $module->difficulty) === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary/50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label for="estimated_duration" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Estimated Duration</label>
                        <input type="text" id="estimated_duration" name="estimated_duration" value="{{ old('estimated_duration', $module->estimated_duration) }}" 
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-sm text-white focus:border-primary focus:bg-surface-light outline-none transition-all placeholder-text-tertiary/30">
                    </div>
                </div>

                <div class="space-y-3">
                    <label for="cover_image" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] ml-1 opacity-60 italic">Module Thumbnail</label>
                    <div class="flex flex-col md:flex-row gap-6">
                        @if($module->cover_image_url)
                            <div class="w-full md:w-48 aspect-video rounded-xl border border-divider overflow-hidden bg-background">
                                <img src="{{ $module->cover_image_url }}" alt="Current Cover" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="flex-1 space-y-3">
                            <div class="relative group">
                                <input type="file" id="cover_image" name="cover_image" accept="image/*"
                                    class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-sm text-white focus:border-primary focus:bg-surface-light outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            </div>
                            <p class="text-[9px] text-text-tertiary opacity-40 ml-1 uppercase tracking-widest italic">Optional: Upload to replace current thumbnail. Recommended: 16:9, Max 2MB</p>
                            @error('cover_image') <p class="text-[10px] font-bold text-red-500 mt-2 uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-surface-light/50 border border-divider rounded-2xl flex items-center justify-between group">
                    <div>
                        <p class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] leading-none mb-2 opacity-60 italic">Publish Status</p>
                        <p class="text-xs text-text-secondary font-bold">Currently {{ $module->is_active ? 'Visible' : 'Hidden' }}</p>
                    </div>
                    <input type="hidden" name="is_active" value="0">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $module->is_active) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-12 h-6 bg-divider rounded-full peer-focus:outline-none peer peer-checked:after:translate-x-full peer-checked:after:border-background after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-text-tertiary after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary peer-checked:after:bg-background"></div>
                    </label>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-divider flex items-center justify-end gap-6">
                <button type="button" onclick="window.history.back()" class="text-[10px] font-black text-text-tertiary hover:text-white uppercase tracking-widest transition-colors">Cancel</button>
                <button type="submit" class="bg-primary hover:bg-primary-dark text-background px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-cyan-glow">Update Module</button>
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
                <h4 class="text-sm font-bold text-white mb-2 uppercase tracking-wide">Delete this Module</h4>
                <p class="text-[11px] text-text-tertiary leading-relaxed max-w-xl">Once deleted, all linked content and assessment tracks will be permanently removed. This action is irreversible.</p>
            </div>
            <form action="{{ route('admin.education.destroy', $module) }}" method="POST" onsubmit="return confirm('CRITICAL: Are you absolutely sure you want to delete this training module? All linked content will be lost.');" class="flex-shrink-0">
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
