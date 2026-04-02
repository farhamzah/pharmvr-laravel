<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VrDevice;
use Illuminate\Http\Request;

class VrDeviceAdminController extends Controller
{
    /**
     * Display a listing of VR devices.
     */
    public function index(Request $request)
    {
        $query = VrDevice::with(['user', 'currentPairing']);

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('device_name', 'like', "%{$search}%")
                  ->orWhere('headset_identifier', 'like', "%{$search}%")
                  ->orWhere('app_version', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $devices = $query->orderBy('last_seen_at', 'desc')->paginate(15)->appends($request->all());

        return view('admin.vr-devices.index', compact('devices'));
    }

    /**
     * Display the specified VR device.
     */
    public function show(VrDevice $vrDevice)
    {
        $vrDevice->load(['user', 'currentPairing']);
        
        $recentSessions = $vrDevice->sessions()
            ->with('trainingModule')
            ->orderBy('started_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.vr-devices.show', compact('vrDevice', 'recentSessions'));
    }

    /**
     * Update the specified VR device in storage.
     */
    public function update(Request $request, VrDevice $vrDevice)
    {
        $validated = $request->validate([
            'device_name' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive,maintenance,retired',
            'notes' => 'nullable|string' // Not in fillable currently, but we can update other fields if needed
        ]);

        $vrDevice->update([
            'device_name' => $validated['device_name'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Hardware unit updated successfully.');
    }

    /**
     * Remove the specified VR device from storage.
     */
    public function destroy(VrDevice $vrDevice)
    {
        // Instead of hard deleting, we might want to just mark it as retired
        // but if we actually want to delete... let's change status for safety.
        $vrDevice->update(['status' => 'retired']);

        return redirect()->route('admin.vr-devices.index')->with('success', 'Hardware unit retired successfully.');
    }
}
