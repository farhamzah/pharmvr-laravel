<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PharmaiConversation;
use App\Models\PharmaiMessage;
use App\Models\VrSession;
use App\Models\VrAiInteraction;
use App\Models\AiUsageLog;
use App\Models\TrainingModule;

class AiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. App Side Scenario: Diverse Conversations
        $aiUser = User::factory()->create([
            'name' => 'Ahmad (Pharmacist)',
            'email' => 'ahmad.gmp@example.com',
        ]);

        // Create 5 Conversations for this user
        PharmaiConversation::factory()
            ->count(5)
            ->for($aiUser)
            ->create()
            ->each(function ($conversation) use ($aiUser) {
                // Seed 4-8 messages per conversation
                $msgCount = rand(4, 8);
                for ($i = 0; $i < $msgCount; $i++) {
                    $message = PharmaiMessage::factory()->create([
                        'conversation_id' => $conversation->id,
                        'role' => $i % 2 === 0 ? 'user' : 'assistant',
                    ]);

                    // Log every assistant response
                    if ($message->role === 'assistant') {
                        AiUsageLog::factory()->create([
                            'user_id' => $aiUser->id,
                            'interaction_type' => 'app_chat',
                            'source_id' => $conversation->id,
                            'prompt_tokens' => $message->metadata['tokens'] / 2,
                            'completion_tokens' => $message->metadata['tokens'] / 2,
                            'total_tokens' => $message->metadata['tokens'],
                            'latency_ms' => $message->metadata['latency_ms'],
                        ]);
                    }
                }
            });

        // 2. VR Side Scenario: Guiding Sessions
        $vrUser = User::where('email', 'vr_user_ready@example.com')->first() 
                  ?? User::factory()->create(['email' => 'vr_user_ready@example.com']);
        
        $module = TrainingModule::where('slug', 'gmp-sterile')->first()
                  ?? TrainingModule::create([
                      'title' => 'GMP Sterile Production',
                      'slug' => 'gmp-sterile',
                      'description' => 'Basics of sterile production',
                      'is_active' => true
                  ]);

        $session = VrSession::factory()->create([
            'user_id' => $vrUser->id,
            'training_module_id' => $module->id,
            'session_status' => 'completed',
        ]);

        // Seed 20 diverse VR Interactions
        $interactions = VrAiInteraction::factory()
            ->count(20)
            ->create([
                'vr_session_id' => $session->id,
            ]);

        foreach ($interactions as $interaction) {
            AiUsageLog::factory()->create([
                'user_id' => $vrUser->id,
                'interaction_type' => 'vr_' . $interaction->hint_type,
                'source_id' => $session->id,
                'prompt_tokens' => $interaction->metadata['tokens'] / 3,
                'completion_tokens' => $interaction->metadata['tokens'] * (2/3),
                'total_tokens' => $interaction->metadata['tokens'],
                'latency_ms' => $interaction->metadata['latency_ms'],
            ]);
        }
    }
}
