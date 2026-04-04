<?php

namespace App\Services;

class VrRuleService
{
    /**
     * Evaluate a VR event against deterministic rules.
     *
     * @param string $eventType
     * @param array $payload
     * @return array
     */
    public function evaluate(string $eventType, array $payload): array
    {
        return match ($eventType) {
            'sterile_breach' => $this->evaluateSterileBreach($payload),
            'wrong_equipment' => $this->evaluateEquipmentChoice($payload),
            'quiz_answer' => $this->evaluateQuizAnswer($payload),
            'gowning_step' => $this->evaluateGowningStep($payload),
            default => [
                'is_correct' => true,
                'rule_id' => 'GENERIC_OK',
                'factual_description' => 'Aksi dilakukan dalam parameter normal.',
                'severity' => 'info'
            ],
        };
    }

    private function evaluateSterileBreach(array $payload): array
    {
        $item = $payload['item'] ?? 'unknown surface';
        return [
            'is_correct' => false,
            'rule_id' => 'GMP-ST-01',
            'factual_description' => "Kontaminasi terdeteksi: Menyentuh $item tanpa sanitasi ulang adalah pelanggaran protokol area steril.",
            'severity' => 'critical'
        ];
    }

    private function evaluateEquipmentChoice(array $payload): array
    {
        $expected = $payload['expected'] ?? '';
        $actual = $payload['actual'] ?? '';

        if ($expected === $actual) {
            return [
                'is_correct' => true,
                'rule_id' => 'GMP-EQ-01',
                'factual_description' => "Peralatan $actual sesuai dengan prosedur.",
                'severity' => 'info'
            ];
        }

        return [
            'is_correct' => false,
            'rule_id' => 'GMP-EQ-02',
            'factual_description' => "Salah peralatan: Menggunakan $actual sementara prosedur mewajibkan $expected.",
            'severity' => 'warning'
        ];
    }

    private function evaluateQuizAnswer(array $payload): array
    {
        $isCorrect = $payload['is_correct'] ?? false;
        return [
            'is_correct' => $isCorrect,
            'rule_id' => 'KNOWLEDGE-CHECK',
            'factual_description' => $isCorrect ? 'Jawaban kuis benar.' : 'Jawaban kuis salah. Prinsip CPOB tidak terpenuhi.',
            'severity' => $isCorrect ? 'info' : 'warning'
        ];
    }

    private function evaluateGowningStep(array $payload): array
    {
        $isCorrect = $payload['is_correct'] ?? true;
        $step = $payload['step_name'] ?? 'tahap gowning';
        
        if ($isCorrect) {
            return [
                'is_correct' => true,
                'rule_id' => 'GMP-GW-01',
                'factual_description' => "Prosedur $step berhasil dilakukan dengan benar.",
                'severity' => 'info'
            ];
        }

        return [
            'is_correct' => false,
            'rule_id' => 'GMP-GW-02',
            'factual_description' => "Kesalahan pada $step: Urutan atau teknik gowning tidak sesuai standar.",
            'severity' => 'warning'
        ];
    }
}
