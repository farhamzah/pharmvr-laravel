<?php

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Models\EducationContent;
use App\Http\Resources\Api\V1\Content\EducationResource;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    /**
     * Display a listing of education contents with filtering.
     */
    public function index(Request $request)
    {
        $type     = $request->query('content_type') ?? $request->query('type');
        $search   = $request->query('search');
        $level    = $request->query('level');
        
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = EducationContent::query()->where('is_active', true);

        if ($type) {
            $query->where('type', $type);
            
            // If filtering for modules, ensure they are linked to a TrainingModule
            if (strtolower($type) === 'module') {
                $query->whereNotNull('training_module_id');
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $category = $request->query('category');
        if ($category) {
            $query->where('category', $category);
        }

        if ($level) {
            $query->where('level', $level);
        }

        $contents = $query->with('trainingModule')->latest()->paginate($request->query('per_page', 50));

        return EducationResource::collection($contents);
    }

    /**
     * Display the specified education item.
     */
    public function show(string $slug)
    {
        $content = EducationContent::where('is_active', true)
            ->where(function($query) use ($slug) {
                $query->where('slug', $slug)
                      ->orWhereHas('trainingModule', function($q) use ($slug) {
                          $q->where('slug', $slug);
                      });
            })
            ->with('trainingModule')
            ->firstOrFail();

        // Fetch related content
        /** @var \Illuminate\Database\Eloquent\Builder $relatedQuery */
        $relatedQuery = EducationContent::where('id', '!=', $content->id)
            ->where('is_active', true)
            ->where(function($q) use ($content) {
                $q->where('category', $content->category)
                  ->orWhere('type', $content->type);
            })
            ->limit(3);

        $related = $relatedQuery->get();

        return (new EducationResource($content))->additional([
            'related_content' => EducationResource::collection($related)
        ]);
    }
}
