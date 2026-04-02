<div class="bg-surface/30 backdrop-blur-md rounded-[32px] border border-divider shadow-premium overflow-hidden h-full flex flex-col group hover:border-primary/20 transition-all duration-500">
    <div class="p-8 border-b border-divider bg-surface-light/10 flex items-center justify-between">
        <div class="flex flex-col">
            <h4 class="font-black text-white text-lg tracking-tight uppercase italic font-display">{{ $title }}</h4>
            <span class="text-[8px] font-black text-text-tertiary uppercase tracking-[0.3em] opacity-40 italic">Configuration Matrix v1.0</span>
        </div>
        <div class="w-10 h-10 rounded-xl bg-background/50 border border-divider flex items-center justify-center text-primary/50 group-hover:text-primary transition-colors">
            @if($type === \App\Enums\AssessmentType::PRETEST)
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @endif
        </div>
    </div>

    @if(!$assessment)
        <div class="p-16 flex-1 flex flex-col items-center justify-center text-center gap-4">
            <div class="w-14 h-14 bg-divider/10 rounded-full flex items-center justify-center mb-2">
                <svg class="w-6 h-6 text-text-tertiary opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <p class="text-text-tertiary italic text-[11px] font-black uppercase tracking-widest opacity-40">Protocol Initialized: NULL</p>
            <button class="mt-2 bg-surface-light border border-divider text-text-tertiary px-6 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed">Initialize Protocol</button>
        </div>
    @else
    <form action="{{ route('admin.assessments.update', $assessment) }}" method="POST" class="p-8 space-y-8 flex-1">
        @csrf @method('PUT')

        <div class="space-y-6">
            <!-- Strategic Header -->
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2 opacity-50 italic">Identification</label>
                    <input type="text" name="title" value="{{ old('title', $assessment->title) }}" class="w-full bg-background/50 border border-divider rounded-2xl px-5 py-3.5 text-sm text-white font-bold focus:border-primary transition-all">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-2 opacity-50 italic">Strategic Summary</label>
                    <textarea name="description" rows="2" class="w-full bg-background/50 border border-divider rounded-2xl px-5 py-3.5 text-xs text-text-tertiary font-medium focus:border-primary transition-all leading-relaxed">{{ old('description', $assessment->description) }}</textarea>
                </div>
            </div>

            <!-- Algorithm Settings -->
            <div class="p-6 bg-background/30 rounded-3xl border border-divider/50 space-y-6">
                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[8px] font-black text-text-tertiary uppercase tracking-widest mb-2 opacity-50">Sample Size</label>
                        <input type="number" name="number_of_questions_to_take" value="{{ old('number_of_questions_to_take', $assessment->number_of_questions_to_take) }}" class="w-full bg-background border border-divider rounded-xl px-4 py-3 text-sm text-white font-black font-display italic focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-[8px] font-black text-text-tertiary uppercase tracking-widest mb-2 opacity-50">Passing Score (%)</label>
                        <input type="number" name="passing_score" value="{{ old('passing_score', $assessment->passing_score) }}" class="w-full bg-background border border-divider rounded-xl px-4 py-3 text-sm text-emerald-400 font-black font-display italic focus:border-emerald-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[8px] font-black text-text-tertiary uppercase tracking-widest mb-2 opacity-50">Time Limit (Mins)</label>
                        <input type="number" name="time_limit_minutes" value="{{ old('time_limit_minutes', $assessment->time_limit_minutes) }}" class="w-full bg-background border border-divider rounded-xl px-4 py-3 text-sm text-amber-400 font-black font-display italic focus:border-amber-500 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center justify-between p-3 bg-surface-light/5 border border-divider rounded-xl">
                        <span class="text-[8px] font-black text-white uppercase tracking-wider">Randomize</span>
                        <label class="relative inline-flex items-center cursor-pointer scale-75">
                            <input type="hidden" name="randomize_questions" value="0">
                            <input type="checkbox" name="randomize_questions" value="1" {{ old('randomize_questions', $assessment->randomize_questions) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-divider/20 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-text-tertiary after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary/50"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-surface-light/5 border border-divider rounded-xl">
                        <span class="text-[8px] font-black text-white uppercase tracking-wider">Permute</span>
                        <label class="relative inline-flex items-center cursor-pointer scale-75">
                            <input type="hidden" name="randomize_options" value="0">
                            <input type="checkbox" name="randomize_options" value="1" {{ old('randomize_options', $assessment->randomize_options) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-divider/20 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-text-tertiary after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary/50"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Activation Protocol -->
            <div class="flex items-center justify-between p-6 bg-background/50 border border-divider rounded-[24px]">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">Interface Status</span>
                    <span class="text-[8px] font-bold text-text-tertiary uppercase tracking-widest opacity-40 italic">Sync with neural feed</span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="status" value="{{ \App\Enums\AssessmentStatus::INACTIVE->value }}">
                    <input type="checkbox" name="status" value="{{ \App\Enums\AssessmentStatus::ACTIVE->value }}" {{ old('status', $assessment->status) === \App\Enums\AssessmentStatus::ACTIVE ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-14 h-8 bg-surface-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-text-tertiary after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary/80 shadow-[inset_0_2px_4px_rgba(0,0,0,0.5)]"></div>
                </label>
            </div>
        </div>

        <div class="pt-8 border-t border-divider">
            <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-background py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all hover:shadow-cyan-glow active:scale-[0.98]">
                Synchronize Matrix
            </button>
        </div>
    </form>
    @endif
</div>
