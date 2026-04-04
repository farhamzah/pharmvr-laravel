@extends('layouts.admin')

@section('header', 'System Protection Active')

@section('content')
<div class="max-w-xl mx-auto py-20 text-center">
    <div class="w-24 h-24 bg-rose-50 text-rose-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-rose-100 ring-8 ring-rose-50">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
    </div>
    <h1 class="text-4xl font-black text-gray-900 mb-4 tracking-tight">Rate Limit Active</h1>
    <p class="text-gray-500 font-medium leading-relaxed mb-8">
        The system has detected an unusual volume of administrative requests from your session. To maintain platform stability and security, further actions are temporarily restricted.
    </p>
    <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 flex items-center gap-4 text-left">
        <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-xs font-black text-indigo-600">ID</div>
        <div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Client Identity</p>
            <p class="text-xs font-mono font-bold text-gray-900">{{ request()->ip() }}</p>
        </div>
    </div>
    <div class="mt-12">
        <a href="{{ route('admin.dashboard') }}" class="px-8 py-4 bg-gray-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-800 transition-all shadow-lg">Refresh Dashboard</a>
    </div>
</div>
@endsection
