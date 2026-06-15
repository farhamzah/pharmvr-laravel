<?php

namespace Database\Seeders;

use App\Models\VrSceneLayout;
use Illuminate\Database\Seeder;

class VrSceneLayoutSeeder extends Seeder
{
    public function run(): void
    {
        VrSceneLayout::updateOrCreate(
            [
                'scene_slug' => 'gmp_standard_room',
                'version' => 1,
            ],
            [
                'title' => 'GMP Standard Room',
                'template_key' => 'cleanroom_standard',
                'status' => VrSceneLayout::STATUS_PUBLISHED,
                'layout_json' => $this->gmpStandardRoomLayout(),
                'metadata_json' => [
                    'module_code' => 'PH-CPOB-00',
                    'content_key' => 'cpob_12_aspects',
                    'source' => 'Backend VR scene layout seed v1',
                    'composer_ready' => true,
                ],
                'validation_warnings_json' => [],
                'published_at' => now(),
            ]
        );
    }

    private function gmpStandardRoomLayout(): array
    {
        return [
            'sceneSlug' => 'gmp_standard_room',
            'contentKey' => 'cpob_12_aspects',
            'title' => 'GMP Standard Room / Ruang Standar CPOB',
            'templateKey' => 'cleanroom_standard',
            'roomBounds' => [
                'minX' => -4.05,
                'maxX' => 4.05,
                'minY' => 0.78,
                'maxY' => 2.72,
                'minZ' => -3.45,
                'maxZ' => 3.35,
            ],
            'components' => [
                $this->component('opening-briefing', 'learning_panel', 'Tujuan Utama', [0, 1.94, -3.81]),
                $this->component('cpob-aspects-panel', 'cms_panel', '12 Aspek CPOB / GMP', [3.55, 1.82, 1.18], [
                    'contentKey' => 'cpob_12_aspects',
                    'locale' => 'id',
                ]),
                $this->component('readiness-checklist', 'checklist_panel', 'Cek CPOB', [-4.18, 1.46, 0.45], [
                    'items' => [
                        'Inspeksi lantai dan dinding mudah dibersihkan',
                        'Kenali fungsi sudut lengkung',
                        'Amati jalur udara suplai dan balik',
                        'Baca tekanan dan kaskade ruang',
                    ],
                ]),
                $this->component('floor-surface', 'hotspot_marker', 'Permukaan Lantai', [-1.55, 0.12, 1.58], [
                    'eventName' => 'gmp_standard_room.floor_inspected',
                ]),
                $this->component('pressure-gauge', 'pressure_gauge', 'Tekanan Ruang', [1.225, 1.41, 3.845], [
                    'eventName' => 'gmp_standard_room.pressure_gauge_checked',
                ]),
                $this->component('equipment-island', 'equipment_island', 'Pulau Peralatan', [0, 0, -0.82]),
            ],
            'learningFlow' => [
                [
                    'id' => 'inspect-room',
                    'title' => 'Inspect cleanroom elements',
                    'requiredComponentIds' => ['floor-surface', 'pressure-gauge'],
                    'evidenceEventTypes' => ['hotspot_inspected'],
                ],
                [
                    'id' => 'readiness-checklist',
                    'title' => 'Complete readiness checklist',
                    'requiredComponentIds' => ['readiness-checklist'],
                    'evidenceEventTypes' => ['checklist_item_completed', 'step_completed'],
                ],
            ],
            'metadata' => [
                'moduleCode' => 'PH-CPOB-00',
                'composerPhase' => 'backend_foundation',
            ],
        ];
    }

    private function component(string $id, string $type, string $title, array $position, array $content = []): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'title' => $title,
            'transform' => [
                'position' => $position,
                'rotation' => [0, 0, 0],
                'scale' => [1, 1, 1],
            ],
            'content' => $content,
        ];
    }
}
