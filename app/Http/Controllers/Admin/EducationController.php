<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingModule;
use App\Models\EducationContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\OptimizesImages;
use App\Services\AssetUrlService;

class EducationController extends Controller
{
    use OptimizesImages;

    /**
     * Display a listing of training modules.
     */
    public function index()
    {
        $modules = TrainingModule::withCount('assessments')->latest()->paginate(10);
        return view('admin.education.index', compact('modules'));
    }

    /**
     * Show the form for creating a new module.
     */
    public function create()
    {
        return view('admin.education.create');
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:training_modules,title',
            'description' => 'required|string',
            'difficulty' => 'required|in:Beginner,Intermediate,Advanced',
            'estimated_duration' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);

        if ($request->hasFile('cover_image')) {
            $path = $this->storeOptimized($request->file('cover_image'), 'modules/thumbnails', 1080);
            $data['cover_image_path'] = $path;
        }

        TrainingModule::create($data);

        return redirect()->route('admin.education.index')->with('success', 'Training module created successfully.');
    }

    /**
     * Show the specified module and its contents.
     */
    public function show(TrainingModule $education)
    {
        $contents = EducationContent::where('training_module_id', $education->id)->get();
        return view('admin.education.show', [
            'module' => $education,
            'contents' => $contents
        ]);
    }

    /**
     * Show the form for editing the specified module.
     */
    public function edit(TrainingModule $education)
    {
        return view('admin.education.edit', ['module' => $education]);
    }

    /**
     * Update the specified module in storage.
     */
    public function update(Request $request, TrainingModule $education)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:training_modules,title,' . $education->id,
            'description' => 'required|string',
            'difficulty' => 'required|in:Beginner,Intermediate,Advanced',
            'estimated_duration' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);

        if ($request->hasFile('cover_image')) {
            // Delete old image if exists
            if ($education->cover_image_path) {
                $cleanPath = AssetUrlService::normalize($education->cover_image_path, 'dynamic');
                \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
            }
            
            $path = $this->storeOptimized($request->file('cover_image'), 'modules/thumbnails', 1080);
            $data['cover_image_path'] = $path;
        }

        $education->update($data);

        return redirect()->route('admin.education.index')->with('success', 'Training module updated successfully.');
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(TrainingModule $education)
    {
        // Check if there are related contents
        if (EducationContent::where('training_module_id', $education->id)->exists()) {
            return back()->with('error', 'Cannot delete module with existing education contents.');
        }

        $education->delete();

        return redirect()->route('admin.education.index')->with('success', 'Training module deleted successfully.');
    }

    /**
     * Show the form for adding content to a module.
     */
    public function addContent(TrainingModule $education)
    {
        return view('admin.education.add-content', ['module' => $education]);
    }

    /**
     * Store education content.
     */
    public function storeContent(Request $request, TrainingModule $education)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Video,Document,Interactive',
            'category' => 'required|string',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'video_url' => 'nullable|url',
            'duration_minutes' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->all();
        $data['training_module_id'] = $education->id;
        $data['code'] = 'EDU-' . strtoupper(Str::random(6));
        $data['slug'] = Str::slug($request->title) . '-' . Str::random(4);

        if ($request->video_url) {
            $videoData = $this->getYouTubeMetadata($request->video_url);
            $data['video_id'] = $videoData['id'];
            $data['platform'] = 'YouTube';
            if (!$request->filled('thumbnail_url')) {
                $data['thumbnail_url'] = $videoData['thumbnail'];
            }
            if (!$request->filled('duration_minutes')) {
                $data['duration_minutes'] = $videoData['duration'];
            }
        }

        EducationContent::create($data);

        return redirect()->route('admin.education.show', $education)->with('success', 'Content added successfully.');
    }

    /**
     * Helper to fetch YouTube metadata.
     */
    private function getYouTubeMetadata($url)
    {
        $id = '';
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            $id = $match[1];
        }

        $metadata = [
            'id' => $id,
            'thumbnail' => "https://img.youtube.com/vi/{$id}/maxresdefault.jpg",
            'duration' => 0,
            'title' => ''
        ];

        try {
            $response = @file_get_contents("https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$id}&format=json");
            if ($response) {
                $json = json_decode($response);
                $metadata['title'] = $json->title ?? '';
            }
        } catch (\Exception $e) {
            // Fallback to defaults
        }

        return $metadata;
    }
}
