<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Dto\AiResponse;

class MockAiProvider implements AiProviderInterface
{
    public function generateResponse(array $messages, array $options = []): AiResponse
    {
        $lastMessage = end($messages)['content'] ?? '';
        $isVr = $options['is_vr'] ?? false;

        if ($isVr) {
            return new AiResponse(
                "Mock VR Hint: Perhatikan kebersihan tangan Anda sebelum menyentuh peralatan sterile.",
                'assistant',
                ['provider' => 'mock', 'latency' => 150]
            );
        }

        // Domain restriction mock check
        if (preg_match('/(siapa|apa|film|politik|makan)/i', $lastMessage) && !preg_match('/(gmp|cpob|sterile|farmasi|obat|training)/i', $lastMessage)) {
            return new AiResponse(
                "Maaf, saya hanya dapat membantu menjawab pertanyaan seputar industri farmasi, GMP/CPOB, dan pelatihan PharmVR.",
                'assistant',
                ['provider' => 'mock', 'latency' => 100]
            );
        }

        return new AiResponse(
            "Ini adalah respon simulasi dari PharmAI. Terkait pertanyaan Anda tentang '$lastMessage', pastikan Anda mengikuti pedoman CPOB yang berlaku.",
            'assistant',
            ['provider' => 'mock', 'latency' => 200]
        );
    }
}
