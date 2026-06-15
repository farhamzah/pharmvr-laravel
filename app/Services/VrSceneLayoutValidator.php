<?php

namespace App\Services;

class VrSceneLayoutValidator
{
    public function validate(string $sceneSlug, array $layout): array
    {
        $errors = [];
        $warnings = [];
        $allowedTypes = config('vr_scene.component_types', []);

        if (($layout['sceneSlug'] ?? $sceneSlug) !== $sceneSlug) {
            $errors['layout_json.sceneSlug'][] = 'The layout sceneSlug must match scene_slug.';
        }

        if (array_key_exists('components', $layout) && !is_array($layout['components'])) {
            $errors['layout_json.components'][] = 'The components field must be an array.';
        }

        $componentIds = [];
        foreach (($layout['components'] ?? []) as $index => $component) {
            if (!is_array($component)) {
                $errors["layout_json.components.{$index}"][] = 'Each component must be an object.';
                continue;
            }

            $id = $component['id'] ?? null;
            if (!is_string($id) || $id === '') {
                $errors["layout_json.components.{$index}.id"][] = 'Each component requires a string id.';
            } elseif (in_array($id, $componentIds, true)) {
                $errors["layout_json.components.{$index}.id"][] = "Duplicate component id [{$id}] is not allowed.";
            } else {
                $componentIds[] = $id;
            }

            $type = $component['type'] ?? null;
            if (!is_string($type) || !in_array($type, $allowedTypes, true)) {
                $errors["layout_json.components.{$index}.type"][] = "Unknown component type [{$type}].";
            }

            $transform = $component['transform'] ?? [];
            if ($transform !== null && !is_array($transform)) {
                $errors["layout_json.components.{$index}.transform"][] = 'The transform field must be an object.';
                continue;
            }

            foreach (['position', 'rotation', 'scale'] as $vectorKey) {
                if (array_key_exists($vectorKey, $transform) && !$this->isVector3($transform[$vectorKey])) {
                    $errors["layout_json.components.{$index}.transform.{$vectorKey}"][] = "The {$vectorKey} value must be an array of 3 numbers.";
                }
            }

            if (!isset($component['title']) && in_array($type, ['learning_panel', 'cms_panel', 'hotspot_marker', 'checklist_panel'], true)) {
                $warnings[] = "Component [{$id}] has no title.";
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function isVector3(mixed $value): bool
    {
        if (!is_array($value) || count($value) !== 3) {
            return false;
        }

        foreach (array_values($value) as $number) {
            if (!is_int($number) && !is_float($number)) {
                return false;
            }
        }

        return true;
    }
}
