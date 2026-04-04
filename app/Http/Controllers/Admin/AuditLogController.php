<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index()
    {
        $logs = AuditLog::with('user')->latest()->paginate(20);
        return view('admin.audit-logs.index', compact('logs'));
    }

    /**
     * Show details of a specific log entry.
     */
    public function show(AuditLog $auditLog)
    {
        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
