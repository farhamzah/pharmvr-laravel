@extends('layouts.admin')

@section('header', 'Audit Log Detail')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="mb-6">
        <a href="{{ route('admin.audit-logs.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Audit Trail
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-gray-50 bg-gray-50/50">
            <div class="flex items-center justify-between mb-4">
                <span class="px-3 py-1 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-full">Entry #{{ $auditLog->id }}</span>
                <span class="text-sm text-gray-500 font-medium">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">
                Action: <span class="text-blue-600 uppercase">{{ $auditLog->action }}</span>
            </h2>
            <p class="text-gray-500 mt-1">Performed by <span class="font-bold text-gray-700">{{ $auditLog->user->name ?? 'System' }}</span></p>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="space-y-6">
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Old State</h4>
                    @if($auditLog->old_values)
                        <pre class="bg-gray-900 text-rose-300 p-4 rounded-xl text-xs overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <div class="p-4 bg-gray-50 rounded-xl text-xs text-gray-400 italic">No previous state (New record)</div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">New State</h4>
                    @if($auditLog->new_values)
                        <pre class="bg-gray-900 text-emerald-300 p-4 rounded-xl text-xs overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <div class="p-4 bg-gray-50 rounded-xl text-xs text-gray-400 italic">Record deleted</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Model</p>
                <p class="text-xs font-bold text-gray-900">{{ class_basename($auditLog->model_type) }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Model ID</p>
                <p class="text-xs font-bold text-gray-900">#{{ $auditLog->model_id }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">User Agent</p>
                <p class="text-[10px] text-gray-500 truncate" title="{{ $auditLog->user_agent }}">{{ $auditLog->user_agent }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
