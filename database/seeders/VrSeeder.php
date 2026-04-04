<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VrDevice;
use App\Models\VrPairing;
use App\Models\VrSession;
use App\Models\TrainingModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VrSeeder extends Seeder
{
    /**
     * Run the VR database seeds.
     */
    public function run(): void
    {
        $testUser = User::where('email', 'test@pharmvr.com')->first();
        $module = TrainingModule::where('slug', 'pengenalan-lab-steril')->first();

        if (!$testUser || !$module) {
            return;
        }

        // 1. Create a Primary Active Device for Test User
        $device = VrDevice::factory()->create([
            'user_id' => $testUser->id,
            'device_name' => "Farhan's Quest 3",
            'device_type' => 'meta_quest_3',
            'status' => 'active',
            'last_seen_at' => now(),
        ]);

        // 2. Create an Active (Playing) Session for Test User
        VrSession::factory()->playing()->create([
            'user_id' => $testUser->id,
            'device_id' => $device->id,
            'training_module_id' => $module->id,
            'progress_percentage' => 45,
            'current_step' => 'sterile_gowning_room',
        ]);

        // 3. Create a Pending Pairing for a New User
        $newUser = User::factory()->create(['name' => 'New VR Student']);
        VrPairing::factory()->create([
            'user_id' => $newUser->id,
            'status' => 'pending',
            'pairing_code_hash' => Hash::make('123456'),
        ]);

        // 4. Create a Completed Session for another User
        $graduatedUser = User::factory()->create(['name' => 'Graduated Student']);
        $gradDevice = VrDevice::factory()->create(['user_id' => $graduatedUser->id]);
        VrSession::factory()->completed()->create([
            'user_id' => $graduatedUser->id,
            'device_id' => $gradDevice->id,
            'training_module_id' => $module->id,
        ]);

        // 5. Create an Interrupted Session for another User
        $interruptedUser = User::factory()->create(['name' => 'Interrupted Student']);
        $intDevice = VrDevice::factory()->state(['last_seen_at' => now()->subHours(2)])->create(['user_id' => $interruptedUser->id]);
        VrSession::factory()->interrupted()->create([
            'user_id' => $interruptedUser->id,
            'device_id' => $intDevice->id,
            'training_module_id' => $module->id,
        ]);

        // 6. Bulk generation for general UI populating
        User::factory()->count(3)->create()->each(function ($u) use ($module) {
            $d = VrDevice::factory()->create(['user_id' => $u->id]);
            VrSession::factory()->count(2)->create([
                'user_id' => $u->id,
                'device_id' => $d->id,
                'training_module_id' => $module->id,
            ]);
        });
    }
}
