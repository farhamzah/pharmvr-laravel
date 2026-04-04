@extends('layouts.admin')

@section('header')
<div class="flex flex-col gap-1">
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-text-tertiary opacity-60 italic">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Intelligence</a>
        <span class="text-primary/30">/</span>
        <a href="{{ route('admin.monitoring.progress') }}" class="hover:text-primary transition-colors">Field Operations</a>
        <span class="text-primary/30">/</span>
        <span class="text-white">Training Progress Matrix</span>
    </div>
    <h1 class="text-2xl font-black text-white tracking-tight font-display uppercase italic">Training Progress Matrix</h1>
</div>
@endsection

@section('content')
<div class="bg-surface rounded-4xl border border-divider shadow-premium overflow-hidden text-sm">
    <div class="p-8 border-b border-divider flex flex-wrap items-center justify-between gap-6 bg-surface-light/30">
        <div>
            <h3 class="font-black text-white text-xl tracking-tight">Performance Matrix</h3>
            <p class="text-xs text-text-tertiary font-bold uppercase tracking-widest mt-1 opacity-60">Tracking student completion across current curriculum</p>
        </div>
        <form action="{{ route('admin.reporting.training') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Find student..." class="px-5 py-3 bg-background border border-divider rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-primary focus:border-transparent outline-none shadow-sm placeholder:text-text-tertiary/50 min-w-[200px]">
            <button type="submit" class="px-6 py-3 bg-primary text-background rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-cyan-glow">Filter</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-surface-light/50 text-[10px] font-black text-text-tertiary uppercase tracking-[0.3em] border-b border-divider">
                <tr>
                    <th class="px-8 py-5 border-b border-divider min-w-[250px]">Student Identity</th>
                    @foreach($modules as $module)
                    <th class="px-4 py-5 border-b border-divider text-center min-w-[120px] group relative cursor-help">
                        {{ Str::limit($module->title, 12) }}
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-background border border-primary/30 text-primary text-[10px] rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none font-bold uppercase tracking-widest">
                            {{ $module->title }}
                        </div>
                    </th>
                    @endforeach
                    <th class="px-8 py-5 border-b border-divider text-right">Aggregate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-divider/50">
                @forelse($students as $student)
                <tr class="hover:bg-primary/5 transition-colors duration-300">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-black text-sm transition-transform hover:scale-110">
                                {{ substr($student->name, 0, 1) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.reporting.user-report', $student) }}" class="text-sm font-bold text-white hover:text-primary transition-colors">{{ $student->name }}</a>
                                <p class="text-[10px] text-text-tertiary font-mono opacity-50">{{ $student->email }}</p>
                            </div>
                        </div>
                    </td>
                    @foreach($modules as $module)
                    <td class="px-4 py-6 border-l border-divider/30 text-center">
                        @php
                            $progress = $student->trainingProgress->where('training_module_id', $module->id)->first();
                            $pPercent = $progress ? $progress->progress_percentage : 0;
                            $isComplete = $pPercent >= 100;
                        @endphp
                        
                        <div class="relative inline-flex items-center justify-center">
                            @if($isComplete)
                                <div class="w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            @elseif($pPercent > 0)
                                <div class="w-10 h-10 rounded-full bg-primary/5 text-primary border border-primary/20 flex items-center justify-center text-[10px] font-black italic shadow-cyan-glow">
                                    {{ $pPercent }}%
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-surface-light text-text-tertiary border border-divider flex items-center justify-center">
                                    <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            @endif
                        </div>
                    </td>
                    @endforeach
                    <td class="px-8 py-6 text-right">
                        @php
                            $avgProgress = $student->trainingProgress->avg('progress_percentage') ?? 0;
                        @endphp
                        <span class="text-sm font-black font-display italic {{ $avgProgress >= 80 ? 'text-emerald-400 drop-shadow-[0_0_8px_rgba(52,211,153,0.5)]' : ($avgProgress >= 50 ? 'text-blue-400 drop-shadow-[0_0_8px_rgba(96,165,250,0.5)]' : 'text-text-tertiary/50') }}">
                            {{ number_format($avgProgress, 0) }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($modules) + 2 }}" class="px-8 py-16 text-center text-[10px] text-text-tertiary font-black uppercase tracking-[0.4em] italic opacity-50">No student data matching criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($students->hasPages())
    <div class="px-8 py-6 bg-surface-light/30 border-t border-divider">
        {{ $students->links() }}
    </div>
    @endif
</div>
@endsection
