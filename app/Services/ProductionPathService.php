<?php

namespace App\Services;

class ProductionPathService
{
    public function scenes(): array
    {
        return config('production_path.scenes', []);
    }

    public function slugs(): array
    {
        return array_values(array_map(
            fn (array $scene) => $scene['slug'],
            $this->scenes()
        ));
    }

    public function scene(string $slug): ?array
    {
        foreach ($this->scenes() as $index => $scene) {
            if ($scene['slug'] === $slug) {
                return [
                    ...$scene,
                    'order' => $index + 1,
                    'previous_slug' => $this->previousSlug($slug),
                    'next_slug' => $this->nextSlug($slug),
                ];
            }
        }

        return null;
    }

    public function previousSlug(string $slug): ?string
    {
        $slugs = $this->slugs();
        $index = array_search($slug, $slugs, true);

        if ($index === false || $index === 0) {
            return null;
        }

        return $slugs[$index - 1];
    }

    public function nextSlug(string $slug): ?string
    {
        $slugs = $this->slugs();
        $index = array_search($slug, $slugs, true);

        if ($index === false || $index >= count($slugs) - 1) {
            return null;
        }

        return $slugs[$index + 1];
    }

    public function isProductionPathScene(string $slug): bool
    {
        return in_array($slug, $this->slugs(), true);
    }
}
