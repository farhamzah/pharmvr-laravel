@extends('layouts.admin')

@section('header', 'System Interruption')

@section('content')
<div class="max-w-xl mx-auto py-20 text-center">
    <div class="w-24 h-24 bg-gray-50 text-gray-400 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-gray-100 ring-8 ring-gray-50">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
    <h1 class="text-4xl font-black text-gray-900 mb-4 tracking-tight">System Exception</h1>
    <p class="text-gray-500 font-medium leading-relaxed mb-8">
        The application encountered an unexpected internal state. For security reasons, the specific details have been obscured, but the incident has been logged for administrative review.
    </p>
    
    <div class="grid grid-cols-2 gap-4 mb-12">
        <div class="bg-white p-6 rounded-3xl border border-gray-100 text-left">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Code</p>
            <p class="text-xl font-black text-gray-900">500</p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 text-left">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Reference ID</p>
            <p class="text-xs font-mono font-bold text-indigo-600">ERR_{{ time() }}</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('admin.dashboard') }}" class="px-8 py-4 bg-gray-900 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-800 transition-all shadow-lg text-center">Return Home</a>
        <button onclick="window.history.back()" class="px-8 py-4 bg-white border border-gray-100 text-gray-900 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 transition-all text-center">Go Back</button>
    </div>
</div>
@endsection
