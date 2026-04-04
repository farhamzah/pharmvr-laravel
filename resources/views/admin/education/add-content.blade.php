@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.education.index') }}" class="hover:text-primary transition-colors">Education</a>
        <span class="text-primary/30">/</span>
        <a href="{{ route('admin.education.show', $module) }}" class="hover:text-primary transition-colors">{{ $module->title }}</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">New Telemetry Stream</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Initialize Content</h1>
</div>
@endsection

@section('content')
<div class="max-w-4xl relative">
    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-primary/10">
        <form action="{{ route('admin.education.store-content', $module) }}" method="POST" class="p-10">
            @csrf

            <!-- Section: Primary Configuration -->
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                    <h3 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] italic">Core Stream Parameters</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-3">
                        <label for="title" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Content Designation (Title)</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required 
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all @error('title') border-red-500 @enderror"
                            placeholder="e.g., CPOB Part 1: Dasar-Dasar">
                    </div>

                    <div class="space-y-3">
                        <label for="type" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Resource Protocol (Type)</label>
                        <select id="type" name="type" class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all appearance-none cursor-pointer">
                            <option value="Video">Video stream</option>
                            <option value="Document">Documentation file</option>
                            <option value="Interactive">Interactive module</option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        <label for="category" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Structural Node (Category)</label>
                        <input type="text" id="category" name="category" value="{{ old('category', 'CPOB') }}" required 
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all">
                    </div>

                    <div class="space-y-3">
                        <label for="level" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Cognitive Depth (Level)</label>
                        <select id="level" name="level" class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all appearance-none cursor-pointer">
                            <option value="Beginner">Entry Level (Beginner)</option>
                            <option value="Intermediate">Field Operative (Intermediate)</option>
                            <option value="Advanced">System Architect (Advanced)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section: Media Configuration -->
            <div class="mb-12 pt-10 border-t border-divider">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-2 h-2 rounded-full bg-primary shadow-cyan-glow"></div>
                    <h3 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] italic">Media Integration</h3>
                </div>

                <div class="space-y-8">
                    <div class="space-y-3">
                        <label for="video_url" class="text-[10px] font-black text-primary uppercase tracking-[0.3em] px-1">YouTube Source Link</label>
                        <div class="relative">
                            <input type="url" id="video_url" name="video_url" value="{{ old('video_url') }}"
                                class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all placeholder:text-text-tertiary/20"
                                placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        <p class="text-[9px] text-text-tertiary italic px-1 opacity-60">System will automatically extract telemetry ID, thumbnail, and metadata.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-3">
                            <label for="duration_minutes" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Runtime Sync (Minutes)</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes') }}"
                                class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all">
                        </div>

                        <div class="space-y-3 flex flex-col justify-end pb-1">
                            <div class="flex items-center justify-between p-4 bg-surface-light/30 border border-divider rounded-2xl">
                                <span class="text-[10px] font-black text-text-tertiary uppercase tracking-widest italic">Stream Status</span>
                                <input type="hidden" name="is_active" value="0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                    <div class="w-10 h-5 bg-surface-light border border-divider peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-background after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-text-tertiary after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-primary peer-checked:after:bg-background"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Data Overview -->
            <div class="space-y-3">
                <label for="description" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Narrative Log (Description)</label>
                <textarea id="description" name="description" rows="4" required
                    class="w-full px-6 py-4 bg-surface-light border border-divider rounded-3xl text-white text-xs font-bold focus:border-primary outline-none transition-all placeholder:text-text-tertiary/20" 
                    placeholder="Provide a detailed summary of this educational stream...">{{ old('description') }}</textarea>
            </div>

            <!-- Submission -->
            <div class="mt-12 pt-10 border-t border-divider flex items-center justify-end gap-6">
                <button type="button" onclick="window.history.back()" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] hover:text-white transition-colors italic">Abort</button>
                <button type="submit" class="bg-primary hover:bg-primary-dark text-background px-10 py-5 rounded-2xl text-[10px] font-black uppercase tracking-[0.4em] transition-all shadow-cyan-glow hover:scale-105">
                    Deploy Content
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
