@extends('layouts.admin')

@section('header', 'Recalibrate Synthetic Persona')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <a href="{{ route('admin.ai.avatars.index') }}" class="flex items-center gap-2 text-[10px] font-black text-text-tertiary hover:text-primary transition-all uppercase tracking-[0.2em]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            Discard Modifications
        </a>
    </div>

    <form action="{{ route('admin.ai.avatars.update', $avatar) }}" method="POST">
        @csrf
        @method('PUT')
        <x-admin.card title="Persona Neural Recalibration" :helper="'ID: ' . strtoupper($avatar->id)">
            <div class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-admin.input-group label="Identity Name" name="name" :value="$avatar->name" required />
                    <x-admin.input-group label="Designated Role" name="role_title" :value="$avatar->role_title" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-text-tertiary uppercase tracking-widest pl-1">Primary Node Integration</label>
                        <select name="default_module_id" class="w-full bg-background border-divider border rounded-2xl px-6 py-4 text-sm font-bold text-white focus:border-primary focus:ring-0 transition-all uppercase tracking-widest cursor-pointer">
                            <option value="">GLOBAL_COGNITION (GENERAL)</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->id }}" {{ $avatar->default_module_id == $module->id ? 'selected' : '' }}>{{ strtoupper($module->title) }}</option>
                            @endforeach
                        </select>
                    </div>
                   <x-admin.input-group label="Voice Synthesis Profile" name="voice_style" :value="$avatar->voice_style" />
                </div>

                <x-admin.input-group label="Persona Neural Architecture" type="textarea" name="persona_text" :value="$avatar->persona_text" required rows="6" />
                
                <x-admin.input-group label="Initialization Greeting" type="textarea" name="greeting_text" :value="$avatar->greeting_text" required rows="3" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <x-admin.input-group label="Vector Mesh Path (Model)" name="avatar_model_path" :value="$avatar->avatar_model_path" />
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-text-tertiary uppercase tracking-widest pl-1">Knowledge Boundaries</label>
                        <input type="text" name="allowed_topics_string" value="{{ is_array($avatar->allowed_topics) ? implode(', ', $avatar->allowed_topics) : '' }}" placeholder="E.G. drugs, symptoms, dosage" class="w-full bg-background border-divider border rounded-2xl px-6 py-4 text-sm font-bold text-white focus:border-primary focus:ring-0 transition-all uppercase tracking-widest">
                    </div>
                </div>

                <div class="flex items-center gap-2 p-6 bg-surface-light/30 rounded-2xl border border-divider">
                    <input type="checkbox" name="is_active" id="is_active" {{ $avatar->is_active ? 'checked' : '' }} class="w-5 h-5 bg-background border-divider rounded text-primary focus:ring-primary ring-offset-background">
                    <label for="is_active" class="text-[10px] font-black text-white uppercase tracking-widest cursor-pointer pl-2">Node Transmission Active</label>
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex items-center justify-end gap-6">
                    <x-admin.ai.action-button type="submit" size="lg">Update Synthesis</x-admin.ai.action-button>
                </div>
            </x-slot>
        </x-admin.card>
    </form>
</div>
@endsection
