@extends('layouts.admin')

@section('header', 'Edit Video Content')

@section('content')
<div class="max-w-4xl relative">
    <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-primary/10">
        <form action="{{ route('admin.videos.update', $video) }}" method="POST" class="p-10">
            @csrf @method('PUT')

            <!-- Section: Primary Configuration -->
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                    <h3 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] italic">Video Content Details</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-3">
                        <label for="title" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Video Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $video->title) }}" required 
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all @error('title') border-red-500 @enderror">
                    </div>

                    <div class="space-y-3">
                        <label for="training_module_id" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Linked Training Module</label>
                        <select id="training_module_id" name="training_module_id" required class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all appearance-none cursor-pointer">
                            @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ $video->training_module_id == $module->id ? 'selected' : '' }}>{{ $module->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-3">
                        <label for="category" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Video Category</label>
                        <input type="text" id="category" name="category" value="{{ old('category', $video->category) }}" required 
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all">
                    </div>

                    <div class="space-y-3">
                        <label for="related_topic" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Educational Topic (Tag)</label>
                        <input type="text" id="related_topic" name="related_topic" value="{{ old('related_topic', $video->related_topic) }}"
                            class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all"
                            placeholder="e.g., Compliance, Quality Assurance">
                    </div>

                    <div class="space-y-3">
                        <label for="level" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Learning Level</label>
                        <select id="level" name="level" class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all appearance-none cursor-pointer">
                            <option value="Beginner" {{ $video->level == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="Intermediate" {{ $video->level == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="Advanced" {{ $video->level == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section: Media Configuration -->
            <div class="mb-12 pt-10 border-t border-divider">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-2 h-2 rounded-full bg-primary shadow-cyan-glow"></div>
                    <h3 class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.4em] italic">Media & Source</h3>
                </div>

                <div class="space-y-8">
                    <div class="space-y-3">
                        <label for="video_url" class="text-[10px] font-black text-primary uppercase tracking-[0.3em] px-1">Video URL (YouTube)</label>
                        <div class="relative">
                            <input type="url" id="video_url" name="video_url" value="{{ old('video_url', 'https://www.youtube.com/watch?v=' . $video->video_id) }}" required
                                class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-3">
                            <label for="duration_minutes" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Duration (Minutes)</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $video->duration_minutes) }}"
                                class="w-full px-6 py-4 bg-surface-light border border-divider rounded-2xl text-white font-bold focus:border-primary focus:bg-surface-light outline-none transition-all">
                        </div>

                        <div class="space-y-3 flex flex-col justify-end pb-1">
                            <div class="flex items-center justify-between p-4 bg-surface-light/30 border border-divider rounded-2xl">
                                <span class="text-[10px] font-black text-text-tertiary uppercase tracking-widest italic">Publication Status</span>
                                <input type="hidden" name="is_active" value="0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ $video->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-10 h-5 bg-surface-light border border-divider peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-background after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-text-tertiary after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-primary peer-checked:after:bg-background"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Data Overview -->
            <div class="space-y-3">
                <label for="description" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] px-1">Content Summary (Description)</label>
                <textarea id="description" name="description" rows="4" required
                    class="w-full px-6 py-4 bg-surface-light border border-divider rounded-3xl text-white text-xs font-bold focus:border-primary outline-none transition-all">{{ old('description', $video->description) }}</textarea>
            </div>

            <!-- Submission -->
            <div class="mt-12 pt-10 border-t border-divider flex items-center justify-end gap-6">
                <button type="button" onclick="window.history.back()" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] hover:text-white transition-colors italic">Cancel</button>
                <button type="submit" class="bg-primary hover:bg-primary-dark text-background px-10 py-5 rounded-2xl text-[10px] font-black uppercase tracking-[0.4em] transition-all shadow-cyan-glow hover:scale-105">
                    Update Video Content
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
