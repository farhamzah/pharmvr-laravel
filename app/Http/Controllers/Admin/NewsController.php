<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Traits\OptimizesImages;

class NewsController extends Controller
{
    use OptimizesImages;

    /**
     * Display a listing of news.
     */
    public function index(Request $request)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = News::latest();

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_active', true)->where('published_at', '<=', now());
            } elseif ($request->status === 'draft') {
                $query->where('is_active', false);
            } elseif ($request->status === 'scheduled') {
                $query->where('is_active', true)->where('published_at', '>', now());
            }
        }

        // Filter by Classification (Category)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'updated':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'published':
                $query->orderBy('published_at', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $news = $query->paginate(10)->withQueryString();
        
        $categories = News::distinct()->pluck('category')->toArray();

        return view('admin.news.index', compact('news', 'categories'));
    }

    /**
     * Show the form for creating a new article.
     */
    public function create()
    {
        return view('admin.news.create');
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:50',
            'published_at' => 'nullable|date',
            'status' => 'required|in:published,draft',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048|dimensions:min_width=800,min_height=450',
        ], [
            'image.required' => 'Gagasan tanpa visualisasi tidak akan diterima. Mohon lampirkan gambar headline.',
            'image.dimensions' => 'Resolusi gambar terlalu rendah. Minimum 800x450px.',
            'image.max' => 'Ukuran file gambar melampaui batas 2MB.',
        ]);

        try {
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $path = $this->storeOptimized($request->file('image'), 'news', 1200);
                $imageUrl = 'storage/' . $path;
            }

            // Generate a unique slug
            $baseSlug = Str::slug($request->title);
            $slug = $baseSlug;
            $counter = 1;
            while (News::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $publishedAt = $request->status === 'published' 
                ? ($request->filled('published_at') ? $request->published_at : now()) 
                : null;

            $news = News::create([
                'title' => $request->title,
                'slug' => $slug,
                'summary' => Str::limit($request->input('content'), 150),
                'content' => $request->input('content'),
                'image_url' => $imageUrl,
                'category' => $request->category ?? 'General',
                'published_at' => $publishedAt,
                'is_active' => $request->status === 'published',
                'is_featured' => false,
            ]);

            return redirect()->route('admin.news.edit', $news->slug)->with('success', 'Intelligence broadcast initiated. Systems ready for preview.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('News Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Critical System Failure: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the article.
     */
    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    /**
     * Update the article in storage.
     */
    public function update(Request $request, News $news)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:50',
            'published_at' => 'nullable|date',
            'status' => 'required|in:published,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048|dimensions:min_width=800,min_height=450',
        ], [
            'image.dimensions' => 'Resolusi gambar terlalu rendah. Minimum 800x450px.',
            'image.max' => 'Ukuran file gambar melampaui batas 2MB.',
        ]);

        try {
            $imageUrl = $news->image_url;
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($news->image_url) {
                    $oldPath = str_replace('storage/', 'public/', $news->image_url);
                    \Illuminate\Support\Facades\Storage::delete($oldPath);
                }

                $path = $this->storeOptimized($request->file('image'), 'news', 1200);
                $imageUrl = 'storage/' . $path;
            }

            // Handle title change and potential slug conflict
            $slug = $news->slug;
            if ($news->title !== $request->title) {
                $baseSlug = Str::slug($request->title);
                $slug = $baseSlug;
                $counter = 1;
                while (News::where('slug', $slug)->where('id', '!=', $news->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
            }

            $publishedAt = $request->status === 'published' 
                ? ($request->filled('published_at') ? $request->published_at : ($news->published_at ?? now())) 
                : null;

            $news->update([
                'title' => $request->title,
                'slug' => $slug,
                'summary' => Str::limit($request->input('content'), 150),
                'content' => $request->input('content'),
                'image_url' => $imageUrl,
                'category' => $request->category ?? 'General',
                'published_at' => $publishedAt,
                'is_active' => $request->status === 'published',
            ]);

            return redirect()->route('admin.news.index')->with('success', 'Intelligence synchronized successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('News Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Critical System Failure: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified article (Preview).
     */
    public function show(News $news)
    {
        // Only admins through the admin portal should see this for now
        if (!Auth::guard('web')->check()) {
            abort(404);
        }
        return view('admin.news.show', compact('news'));
    }

    /**
     * Remove the article from storage.
     */
    public function destroy(News $news)
    {
        if ($news->image_url) {
            $oldPath = str_replace('storage/', 'public/', $news->image_url);
            \Illuminate\Support\Facades\Storage::delete($oldPath);
        }
        
        $news->delete();
        return redirect()->route('admin.news.index')->with('success', 'News article deleted successfully.');
    }
}
