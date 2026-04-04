<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'home_banner',
                'value' => 'assets/images/hero_banner.jpg',
                'group' => 'Appearance',
                'type' => 'image',
                'description' => 'The main banner image displayed on the home screen.',
            ],
            [
                'key' => 'about_mission',
                'value' => 'PharmVR is a next-generation VR-centered learning platform designed specifically for the pharmaceutical industry.',
                'group' => 'About',
                'type' => 'textarea',
                'description' => 'Our mission statement displayed on the About screen.',
            ],
            [
                'key' => 'about_description',
                'value' => 'By merging Good Manufacturing Practice (GMP/CPOB) standards with immersive VR simulations and an intelligent AI assistant, PharmVR bridges the gap between theoretical knowledge and practical training.',
                'group' => 'About',
                'type' => 'textarea',
                'description' => 'Detailed description of PharmVR.',
            ],
            [
                'key' => 'privacy_policy_url',
                'value' => 'https://pharmvr.id/privacy',
                'group' => 'Legal',
                'type' => 'text',
                'description' => 'Link to the Privacy Policy.',
            ],
            [
                'key' => 'terms_of_service_url',
                'value' => 'https://pharmvr.id/terms',
                'group' => 'Legal',
                'type' => 'text',
                'description' => 'Link to the Terms of Service.',
            ],
            [
                'key' => 'official_website_url',
                'value' => 'https://pharmvr.id',
                'group' => 'Legal',
                'type' => 'text',
                'description' => 'Link to the Official Website.',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Setting::whereIn('key', [
            'home_banner',
            'about_mission',
            'about_description',
            'privacy_policy_url',
            'terms_of_service_url',
            'official_website_url'
        ])->delete();
    }
};
