<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Ai\Providers\AiProviderInterface;
use App\Services\Ai\Providers\MockAiProvider;
use App\Services\Ai\AiPromptBuilder;
use App\Services\Ai\AiChatService;
use App\Services\Ai\VrAiGuideService;

class AiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AiProviderInterface::class, function ($app) {
            // Future: check config for 'openai' or 'gemini'
            return new MockAiProvider();
        });

        $this->app->singleton(AiPromptBuilder::class, function ($app) {
            return new AiPromptBuilder();
        });

        $this->app->singleton(AiChatService::class, function ($app) {
            return new AiChatService(
                $app->make(AiProviderInterface::class),
                $app->make(AiPromptBuilder::class)
            );
        });

        $this->app->singleton(\App\Services\VrRuleService::class, function ($app) {
            return new \App\Services\VrRuleService();
        });

        $this->app->singleton(VrAiGuideService::class, function ($app) {
            return new VrAiGuideService(
                $app->make(AiProviderInterface::class),
                $app->make(AiPromptBuilder::class),
                $app->make(\App\Services\VrRuleService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
