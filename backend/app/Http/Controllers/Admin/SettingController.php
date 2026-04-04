<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Traits\OptimizesImages;
use App\Services\AssetUrlService;

class SettingController extends Controller
{
    use OptimizesImages;

    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $settings = Setting::all();

        foreach ($settings as $setting) {
            if ($setting->type === 'image') {
                if ($request->hasFile($setting->key)) {
                    // Delete old image if exists and it's not a default asset
                    if ($setting->value && !str_contains($setting->value, 'assets/')) {
                        $cleanValue = AssetUrlService::normalize($setting->value, 'dynamic');
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanValue);
                    }

                    $path = $this->storeOptimized($request->file($setting->key), 'settings', 1200);
                    $setting->update(['value' => $path]);
                }
            } else {
                if ($request->has($setting->key)) {
                    $setting->update(['value' => $request->input($setting->key)]);
                }
            }
        }

        return redirect()->back()->with('success', 'Platform settings updated successfully.');
    }
}
