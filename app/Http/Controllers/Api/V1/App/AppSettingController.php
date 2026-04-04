<?php

namespace App\Http\Controllers\Api\V1\App;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    /**
     * Get all app settings.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'message' => 'App settings successfully retrieved.',
            'data'    => [
                'about_mission'        => $settings['about_mission'] ?? '',
                'about_description'    => $settings['about_description'] ?? '',
                'privacy_policy_url'   => $settings['privacy_policy_url'] ?? '',
                'terms_of_service_url' => $settings['terms_of_service_url'] ?? '',
                'official_website_url' => $settings['official_website_url'] ?? '',
            ]
        ]);
    }
}
