@php 
    $isEdit = isset($question); 
    $modalId = $isEdit ? 'edit-question-' . $question->id : 'create-question'; 
@endphp

<div x-show="open" 
     x-on:open-modal.window="if ($event.detail === '{{ $modalId }}') open = true" 
     x-on:close-modal.window="open = false" 
     x-data="{ open: false, correctOption: '{{ $isEdit ? ($question->options->where('is_correct', true)->first()?->option_key ?? 'A') : 'A' }}' }" 
     class="fixed inset-0 z-[100] overflow-y-auto" 
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-background/95 backdrop-blur-3xl transition-opacity" @click="open = false"></div>

    <!-- Modal Content -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-surface border border-divider rounded-[40px] shadow-2xl w-full max-w-4xl overflow-hidden relative group">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-primary/50 to-transparent"></div>
            
            <form action="{{ $isEdit ? route('admin.assessments.questions.update', [$module, $question]) : route('admin.assessments.questions.store', $module) }}" method="POST" class="flex flex-col">
                @csrf
                @if($isEdit) @method('PUT') @endif

                <!-- Modal Header -->
                <div class="p-10 border-b border-divider flex items-center justify-between bg-surface-light/10">
                    <div class="flex flex-col">
                        <h3 class="text-2xl font-black text-white font-display italic tracking-widest uppercase">
                            {{ $isEdit ? 'Neural Logic Reconfiguration' : 'Neural Asset Encoding' }}
                        </h3>
                        <span class="text-[9px] font-black text-text-tertiary uppercase tracking-[0.4em] opacity-40 mt-1 italic">Protocol Sector: {{ $module->slug }}</span>
                    </div>
                    <button type="button" @click="open = false" class="p-4 bg-background/50 border border-divider rounded-2xl text-text-tertiary hover:text-white transition-all hover:rotate-90">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-10 space-y-10 overflow-y-auto max-h-[70vh]">
                    <!-- Core Question Data -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                        <div class="md:col-span-2 space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-3 opacity-60">Neural Inquiry String</label>
                                <textarea name="question_text" rows="4" required class="w-full bg-background/50 border border-divider rounded-3xl px-6 py-5 text-base text-white font-bold focus:border-primary transition-all leading-relaxed">{{ old('question_text', $question->question_text ?? '') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-3 opacity-60">Strategic Explanation (Optional)</label>
                                <textarea name="explanation" rows="2" class="w-full bg-background/50 border border-divider rounded-2xl px-6 py-4 text-sm text-text-tertiary font-medium focus:border-primary transition-all">{{ old('explanation', $question->explanation ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div>
                                <label class="block text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] mb-3 opacity-60">Protocol Scope</label>
                                <select name="usage_scope" class="w-full bg-background border border-divider rounded-2xl px-6 py-4 text-xs font-black text-white uppercase tracking-widest focus:border-primary transition-all appearance-none cursor-pointer">
                                    <option value="{{ \App\Enums\QuestionUsageScope::BOTH->value }}" {{ old('usage_scope', $question->usage_scope->value ?? '') === \App\Enums\QuestionUsageScope::BOTH->value ? 'selected' : '' }}>Universal Feed</option>
                                    <option value="{{ \App\Enums\QuestionUsageScope::PRETEST->value }}" {{ old('usage_scope', $question->usage_scope->value ?? '') === \App\Enums\QuestionUsageScope::PRETEST->value ? 'selected' : '' }}>Pre-Test Only</option>
                                    <option value="{{ \App\Enums\QuestionUsageScope::POSTTEST->value }}" {{ old('usage_scope', $question->usage_scope->value ?? '') === \App\Enums\QuestionUsageScope::POSTTEST->value ? 'selected' : '' }}>Post-Test Only</option>
                                </select>
                            </div>

                            <div class="p-6 bg-background/50 border border-divider rounded-3xl flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-white uppercase tracking-widest">Active</span>
                                    <span class="text-[8px] font-bold text-text-tertiary uppercase opacity-40">Visibility</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $question->is_active ?? true) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-12 h-6 bg-surface-light rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-primary/80 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-text-tertiary after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Neural Options Permutation -->
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-[10px] font-black text-primary uppercase tracking-[0.4em] italic">Option Permutation Matrix</h4>
                            <span class="text-[8px] font-black text-text-tertiary uppercase tracking-widest opacity-40">Selection: Exactly 1 Correct</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach(['A', 'B', 'C', 'D'] as $key)
                            @php 
                                $optionValue = $isEdit ? ($question->options->where('option_key', $key)->first()?->option_text ?? '') : '';
                            @endphp
                            <div class="relative group/opt">
                                <div :class="correctOption === '{{ $key }}' ? 'border-emerald-500/50 bg-emerald-500/[0.03]' : 'border-divider bg-background/30'" 
                                     class="flex items-center gap-4 p-2 rounded-3xl border transition-all duration-300">
                                    
                                    <div @click="correctOption = '{{ $key }}'" 
                                         class="shrink-0 w-12 h-12 rounded-2xl border border-divider flex items-center justify-center cursor-pointer transition-all group-hover/opt:border-primary/50 overflow-hidden relative">
                                        <input type="radio" name="correct_option" value="{{ $key }}" x-model="correctOption" class="sr-only">
                                        <span class="text-sm font-black text-white z-10 font-mono tracking-tighter">{{ $key }}</span>
                                        <div x-show="correctOption === '{{ $key }}'" 
                                             class="absolute inset-0 bg-emerald-500 shadow-cyan-glow animate-in zoom-in duration-300"></div>
                                        <div x-show="correctOption === '{{ $key }}'" 
                                             class="absolute inset-0 flex items-center justify-center z-10 text-background">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>
                                    
                                    <input type="text" name="options[{{ $key }}]" value="{{ old("options.$key", $optionValue) }}" required placeholder="Input neural option string..." 
                                           class="flex-1 bg-transparent border-none text-white text-sm font-bold placeholder:text-text-tertiary/20 focus:ring-0">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-10 border-t border-divider bg-surface-light/5 flex items-center justify-end gap-6">
                    <button type="button" @click="open = false" class="text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] hover:text-white transition-colors">Abort</button>
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-background px-12 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] transition-all shadow-cyan-glow active:scale-95">
                        {{ $isEdit ? 'Re-Sync Module' : 'Commit Neural Asset' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
