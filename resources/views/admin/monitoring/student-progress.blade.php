@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Monitoring</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Student Progress Matrix</h1>
</div>
@endsection

@section('content')
<div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden">
    <div class="p-8 border-b border-divider flex items-center justify-between bg-surface-light/30">
        <h3 class="font-black text-white text-xl tracking-tight uppercase italic font-display">Module Completion Hub</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] italic">
                <tr>
                    <th class="px-8 py-5 border-b border-divider/50 sticky left-0 bg-surface-light min-w-[250px]">Student / Module</th>
                    @foreach($modules as $module)
                        <th class="px-8 py-5 border-b border-divider/50 text-center min-w-[150px]">
                            <div class="truncate max-w-[140px]">{{ $module->title }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-divider/30">
                @forelse($users as $user)
                <tr class="hover:bg-primary/5 transition-all duration-300 group">
                    <td class="px-8 py-6 sticky left-0 bg-surface group-hover:bg-surface-light/80 transition-all border-r border-divider/20">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center text-primary font-black italic shadow-cyan-glow/20">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white uppercase italic tracking-tight">{{ $user->name }}</div>
                                <div class="text-[9px] text-text-tertiary font-black uppercase tracking-widest mt-0.5 opacity-50">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    @foreach($modules as $module)
                        @php
                            $prog = $user->trainingProgress->where('training_module_id', $module->id)->first();
                        @endphp
                        <td class="px-8 py-6 text-center">
                            @if($prog)
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Pre Test -->
                                    <div @class([
                                        'w-5 h-5 rounded-md flex items-center justify-center text-[8px] font-black border',
                                        'bg-primary/20 text-primary border-primary/30 shadow-cyan-glow/20' => $prog->pre_test_status === 'passed',
                                        'bg-red-500/20 text-red-500 border-red-500/30' => $prog->pre_test_status === 'failed',
                                        'bg-surface-light text-text-tertiary border-divider opacity-40' => $prog->pre_test_status === 'locked' || $prog->pre_test_status === 'available',
                                    ]) title="Pre-Test: {{ $prog->pre_test_status }}">P</div>
                                    
                                    <!-- VR Sim -->
                                    <div @class([
                                        'w-5 h-5 rounded-md flex items-center justify-center text-[8px] font-black border',
                                        'bg-primary/20 text-primary border-primary/30 shadow-cyan-glow/20' => $prog->vr_status === 'completed',
                                        'bg-surface-light text-text-tertiary border-divider opacity-40' => $prog->vr_status !== 'completed',
                                    ]) title="VR Simulation: {{ $prog->vr_status }}">V</div>

                                    <!-- Post Test -->
                                    <div @class([
                                        'w-5 h-5 rounded-md flex items-center justify-center text-[8px] font-black border',
                                        'bg-primary/20 text-primary border-primary/30 shadow-cyan-glow/20' => $prog->post_test_status === 'passed',
                                        'bg-red-500/20 text-red-500 border-red-500/30' => $prog->post_test_status === 'failed',
                                        'bg-surface-light text-text-tertiary border-divider opacity-40' => $prog->post_test_status === 'locked' || $prog->post_test_status === 'available',
                                    ]) title="Post-Test: {{ $prog->post_test_status }}">R</div>
                                </div>
                                @if($prog->completion_percentage == 100)
                                    <div class="mt-2 text-[8px] font-black text-primary uppercase tracking-widest opacity-60">Success</div>
                                @else
                                    <div class="mt-2 text-[8px] font-black text-text-tertiary uppercase tracking-widest opacity-40">{{ $prog->completion_percentage }}%</div>
                                @endif
                            @else
                                <span class="text-[10px] text-text-tertiary font-black opacity-10 tracking-[0.2em] italic">- LOCKED -</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($modules) + 1 }}" class="px-8 py-20 text-center text-text-tertiary font-black uppercase tracking-[0.4em] opacity-30 italic">No student records detected.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Premium Table Styles */
    .sticky-left-shadow {
        box-shadow: 10px 0 15px -10px rgba(0,0,0,0.5);
    }
</style>
@endsection
