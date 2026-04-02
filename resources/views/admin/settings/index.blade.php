@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Governance</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">System Configurations</h1>
</div>
@endsection

@section('content')
<div class="max-w-4xl relative">
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf
        @method('PUT')

        @foreach($settings as $group => $items)
            <div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden transition-all hover:border-primary/20">
                <div class="px-10 py-6 border-b border-divider bg-surface-light/30">
                    <h3 class="text-[10px] font-black text-primary uppercase tracking-[0.4em] italic">{{ $group }} Control Protocol</h3>
                </div>
                <div class="p-10 space-y-10">
                    @foreach($items as $setting)
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-8 group">
                            <div class="max-w-md">
                                <label for="{{ $setting->key }}" class="block text-sm font-bold text-white mb-2 capitalize tracking-tight group-hover:text-primary transition-colors">
                                    {{ str_replace('_', ' ', $setting->key) }}
                                </label>
                                <p class="text-xs text-text-tertiary leading-relaxed opacity-70">{{ $setting->description }}</p>
                            </div>
                            
                            <div class="w-full md:w-64">
                                @if($setting->type === 'boolean')
                                    <div class="relative">
                                        <select name="{{ $setting->key }}" id="{{ $setting->key }}" class="w-full bg-surface-light border border-divider text-white text-xs rounded-2xl focus:ring-primary focus:border-primary block p-4 font-black uppercase tracking-widest appearance-none transition-all hover:bg-surface-light/80 cursor-pointer">
                                            <option value="true" {{ $setting->value === 'true' ? 'selected' : '' }}>Verified (True)</option>
                                            <option value="false" {{ $setting->value === 'false' ? 'selected' : '' }}>Override (False)</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-primary">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </div>
                                @elseif($setting->type === 'integer')
                                    <input type="number" name="{{ $setting->key }}" id="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full bg-surface-light border border-divider text-white text-sm rounded-2xl focus:ring-primary focus:border-primary block p-4 font-bold transition-all placeholder-text-tertiary/30 outline-none">
                                @elseif($setting->type === 'textarea')
                                    <textarea name="{{ $setting->key }}" id="{{ $setting->key }}" rows="4" class="w-full bg-surface-light border border-divider text-white text-sm rounded-2xl focus:ring-primary focus:border-primary block p-4 font-medium transition-all placeholder-text-tertiary/30 outline-none resize-none">{{ $setting->value }}</textarea>
                                @elseif($setting->type === 'image')
                                    <div class="space-y-4">
                                        @if($setting->value)
                                            <div class="relative w-full aspect-video rounded-xl overflow-hidden border border-divider bg-black/20">
                                                <img src="{{ str_contains($setting->value, 'http') ? $setting->value : asset($setting->value) }}" class="w-full h-full object-cover">
                                            </div>
                                        @endif
                                        <div class="relative group/upload">
                                            <input type="file" name="{{ $setting->key }}" id="{{ $setting->key }}" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <div class="w-full bg-surface-light border border-divider text-text-tertiary text-[10px] font-black uppercase tracking-widest rounded-2xl p-4 flex items-center justify-center gap-2 group-hover/upload:border-primary/50 transition-all group-hover/upload:text-primary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                Upload Content
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full bg-surface-light border border-divider text-white text-sm rounded-2xl focus:ring-primary focus:border-primary block p-4 font-bold transition-all placeholder-text-tertiary/30 outline-none">
                                @endif
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="border-divider/50">
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex items-center justify-end gap-6 p-10 bg-surface rounded-4xl border border-divider shadow-premium">
            <button type="reset" class="px-8 py-4 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] hover:text-white transition-colors italic">Reset Node</button>
            <button type="submit" class="px-10 py-4 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-[0.4em] hover:scale-105 transition-all shadow-cyan-glow">
                Commit Changes
            </button>
        </div>
    </form>
</div>
@endsection
