<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiUsageLog企鹅>
 */
class AiUsageLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $interactionTypes = ['app_chat', 'vr_hint', 'vr_reminder', 'vr_feedback'];
        $type = $this->faker->randomElement($interactionTypes);

        return [
            'user_id' => \App\Models\User::factory(),
            'interaction_type' => $type,
            'source_type' => 'App\Models\PharmaiConversation',
            'source_id' => rand(1, 1000), // Placeholder for conversation or session ID
            'provider_name' => $this->faker->randomElement(['openai', 'google', 'mock']),
            'model_name' => 'gpt-4o',
            'prompt_tokens' => rand(100, 500),
            'completion_tokens' => rand(50, 300),
            'total_tokens' => function (array $attributes) {
                return $attributes['prompt_tokens'] + $attributes['completion_tokens'];
            },
            'latency_ms' => rand(500, 3000),
            'domain_mode' => 'pharmacist_guide',
            'is_safe_response' => true,
            'metadata' => [
                'safety_flags' => ['guardrail_passed' => true],
                'ip' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
            ],
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
