<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Enable to block non-admin users from using the application.'
            ],
            [
                'key' => 'vr_bypass_default',
                'value' => 'false',
                'group' => 'vr',
                'type' => 'boolean',
                'description' => 'Allow new users to bypass module prerequisites by default.'
            ],
            [
                'key' => 'session_timeout',
                'value' => '3600',
                'group' => 'security',
                'type' => 'integer',
                'description' => 'User session duration in seconds.'
            ],
            [
                'key' => 'support_email',
                'value' => 'support@pharmvr.pro',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Primary contact for user support inquiries.'
            ],
            [
                'key' => 'max_assessment_attempts',
                'value' => '3',
                'group' => 'education',
                'type' => 'integer',
                'description' => 'Default maximum attempts allowed for any assessment.'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
