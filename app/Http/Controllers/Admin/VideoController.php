<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\EducationContent;
use App\Models\TrainingModule;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    /**
     * Display a listing of video resources.
     */
    public function index()
    {
        $videos = EducationContent::where('type', 'Video')->with('trainingModule')->latest()->paginate(10);
        return view('admin.videos.index', compact('videos'));
    }

    /**
     * Show form to create new video.
     */
    public function create()
    {
        $modules = TrainingModule::all();
        return view('admin.videos.create', compact('modules'));
    }

    /**
     * Store a newly created video.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'training_module_id' => 'required|exists:training_modules,id',
            'category' => 'required|string',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'related_topic' => 'nullable|string|max:255',
            'video_url' => 'required|url',
            'duration_minutes' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->all();
        $data['type'] = 'Video';
        $data['code'] = 'EDU-VID-' . strtoupper(Str::random(5));
        $data['slug'] = Str::slug($request->title) . '-' . Str::random(4);

        $videoData = $this->getYouTubeMetadata($request->video_url);
        $data['video_id'] = $videoData['id'];
        $data['platform'] = 'YouTube';
        
        if (!$request->filled('thumbnail_url')) {
            $data['thumbnail_url'] = $videoData['thumbnail'];
        }
        
        if (!$request->filled('duration_minutes')) {
            $data['duration_minutes'] = $videoData['duration'];
        }

        EducationContent::create($data);

        return redirect()->route('admin.videos.index')->with('success', 'Video content has been added successfully.');
    }

    /**
     * Show form to edit video.
     */
    public function edit(EducationContent $video)
    {
        $modules = TrainingModule::all();
        return view('admin.videos.edit', compact('video', 'modules'));
    }

    /**
     * Update existing video.
     */
    public function update(Request $request, EducationContent $video)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'training_module_id' => 'required|exists:training_modules,id',
            'category' => 'required|string',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'related_topic' => 'nullable|string|max:255',
            'video_url' => 'required|url',
            'duration_minutes' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->all();
        
        if ($request->video_url !== "https://www.youtube.com/watch?v={$video->video_id}") {
            $videoData = $this->getYouTubeMetadata($request->video_url);
            $data['video_id'] = $videoData['id'];
            if (!$request->filled('thumbnail_url')) {
                $data['thumbnail_url'] = $videoData['thumbnail'];
            }
        }

        $video->update($data);

        return redirect()->route('admin.videos.index')->with('success', 'Video content updated.');
    }

    /**
     * Remove video.
     */
    public function destroy(EducationContent $video)
    {
        $video->delete();
        return redirect()->route('admin.videos.index')->with('success', 'Video content has been removed.');
    }

    /**
     * Toggle video status.
     */
    public function toggleStatus(EducationContent $video)
    {
        $video->update(['is_active' => !$video->is_active]);
        $status = $video->is_active ? 'published' : 'unpublished';
        return back()->with('success', "Video is now {$status}.");
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
            'thumbnail' => "https://img.youtube.com/vi/{$id}/hqdefault.jpg",
            'duration' => 0,
            'title' => ''
        ];

        try {
            $response = @file_get_contents("https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$id}&format=json");
            if ($response) {
                $json = json_decode($response);
                $metadata['title'] = $json->title ?? '';
            }
        } catch (\Exception $e) {}

        return $metadata;
    }
}
