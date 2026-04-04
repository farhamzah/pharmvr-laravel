<?php

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Http\Resources\Api\V1\Content\NewsResource;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of active news.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $category = $request->query('category');
        $search = $request->query('search');
        $featured = $request->boolean('is_featured');
        $type = $request->query('type');
        $topic = $request->query('topic');
        $source = $request->query('source');

        $query = News::visible();

        if ($type) {
            $query->where('content_type', $type);
        }

        if ($topic) {
            $query->where(function ($q) use ($topic) {
                $q->where('topic_category', $topic);

                // Map UI Topics to existing Internal News Categories
                if ($topic === 'Pharma Industry') {
                    $q->orWhere('category', 'Industry');
                } elseif ($topic === 'GMP') {
                    $q->orWhere('category', 'Regulation');
                } else {
                    $q->orWhere('category', $topic);
                }
            });
        }

        if ($source) {
            $query->whereHas('source', function ($q) use ($source) {
                $q->where('slug', $source);
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $featured);
        }

        // Pinned articles always appear first, then sorted by published date
        $query->orderBy('is_pinned', 'desc')->latest('published_at');

        $news = $query->paginate($perPage);

        return NewsResource::collection($news);
    }

    /**
     * Display the specified news item.
     */
    public function show($slug)
    {
        $news = News::visible()->where('slug', $slug)->firstOrFail();
        
        // Also fetch related news
        $related = News::visible()
            ->where('id', '!=', $news->id)
            ->where(function ($query) use ($news) {
                $query->where('category', $news->category)
                      ->orWhere('topic_category', $news->topic_category);
            })
            ->latest('published_at')
            ->take(3)
            ->get();
            
        return response()->json([
            'data' => new NewsResource($news),
            'related' => NewsResource::collection($related)
        ]);
    }

    public function sources()
    {
        $sources = \App\Models\NewsSource::where('is_active', true)->select('name', 'slug')->get();
        return response()->json(['data' => $sources]);
    }

    public function categories()
    {
        $internal = News::visible()->whereNotNull('category')->distinct()->pluck('category');
        $external = News::visible()->whereNotNull('topic_category')->distinct()->pluck('topic_category');
        
        $all = $internal->concat($external)->unique()->values();
        
        return response()->json(['data' => $all]);
    }
}
