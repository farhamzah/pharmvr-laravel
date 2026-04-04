@extends('layouts.admin')

@section('header', 'Student Performance Report')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-12">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reporting.training') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Matrix
        </a>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-white border border-gray-100 rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-600 hover:bg-gray-50">Export PDF</button>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700">Message Student</button>
        </div>
    </div>

    <!-- Student Profile Hero -->
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-8 flex flex-col md:flex-row gap-8 items-center bg-gradient-to-br from-white to-gray-50/50">
        <div class="w-32 h-32 rounded-[2.5rem] bg-indigo-600 text-white flex items-center justify-center text-4xl font-black shadow-xl shadow-indigo-100 ring-8 ring-indigo-50">
            {{ substr($user->name, 0, 1) }}
        </div>
        <div class="flex-1 text-center md:text-left">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">{{ $user->name }}</h2>
            <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-2">
                <span class="px-3 py-1 bg-gray-900 text-white rounded-full text-[10px] font-black uppercase tracking-widest">{{ $user->email }}</span>
                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest">Active Student</span>
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-widest">Joined {{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 w-full md:w-auto">
            <div class="bg-white p-4 rounded-2xl border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pass Rate</p>
                <p class="text-xl font-black text-gray-900">89%</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rank</p>
                <p class="text-xl font-black text-emerald-600">Top 5%</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Training Progress -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-gray-50">
                    <h3 class="font-black text-gray-900 text-xl">Curriculum Progress</h3>
                </div>
                <div class="p-8 space-y-8">
                    @forelse($user->trainingProgress as $progress)
                    <div class="space-y-3">
                        <div class="flex justify-between items-end">
                            <div>
                                <h4 class="text-sm font-black text-gray-900">{{ $progress->module->title }}</h4>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Last Access: {{ $progress->updated_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-xs font-black {{ $progress->progress_percentage >= 100 ? 'text-emerald-600' : 'text-blue-600' }}">
                                {{ $progress->progress_percentage }}%
                            </span>
                        </div>
                        <div class="w-full h-2 bg-gray-50 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full" @style(['width' => $progress->progress_percentage . '%'])></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-400 italic py-8">No training progress recorded for this user.</p>
                    @endforelse
                </div>
            </div>

            <!-- Assessment History -->
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-gray-50">
                    <h3 class="font-black text-gray-900 text-xl">Assessment Performance</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-4">Assessment</th>
                                <th class="px-8 py-4">Score</th>
                                <th class="px-8 py-4">Status</th>
                                <th class="px-8 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($user->assessmentAttempts as $attempt)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="text-sm font-bold text-gray-900">{{ $attempt->assessment->title }}</div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $attempt->assessment->type }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-black {{ $attempt->score >= 80 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $attempt->score }}/100</div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-gray-50 text-gray-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                                        {{ $attempt->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right flex items-center justify-end gap-3">
                                    <div class="text-xs font-bold text-gray-400 mr-2">{{ $attempt->created_at->format('M d, Y') }}</div>
                                    <form action="{{ route('admin.assessments.reset-attempts', [$attempt->assessment_id, $user->id]) }}" method="POST" onsubmit="return confirm('Reset all attempts for this user? Data will be permanently lost.')">
                                        @csrf
                                        <button type="submit" class="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Reset Attempts">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-10 text-center text-gray-400 italic">No assessment attempts recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Meta Info & Behavioral Insights -->
        <div class="space-y-8">
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-8">
                <h3 class="font-black text-gray-900 text-lg mb-6">Device Insights</h3>
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 21h6l-.75-4M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Primary Device</p>
                            <p class="text-xs font-bold text-gray-900">Meta Quest 3 (Enterprise)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total VR Time</p>
                            <p class="text-xs font-bold text-gray-900">14h 22m</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-100">
                <h3 class="font-black text-lg mb-4">AI Tutor Feedback</h3>
                <p class="text-xs text-indigo-100 italic leading-relaxed mb-6">"Student shows high engagement with pharmaceutical compliance modules. Recommend moving to advanced GMP scenarios."</p>
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest">Powered by PharmAI</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
