<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiKnowledgeSource;
use App\Http\Requests\Admin\Ai\StoreAiKnowledgeSourceRequest;
use App\Http\Requests\Admin\Ai\UpdateAiKnowledgeSourceRequest;
use App\Services\Ai\AiSourceIngestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiKnowledgeSourceController extends Controller
{
    protected $ingestionService;

    public function __construct(AiSourceIngestionService $ingestionService)
    {
        $this->ingestionService = $ingestionService;
    }

    public function index(Request $request)
    {
        $query = AiKnowledgeSource::with('module', 'uploader')
            ->withCount('chunks');

        // Apply filters
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('trust_level')) {
            $query->where('trust_level', $request->trust_level);
        }

        $sources = $query->latest()->paginate(15)->withQueryString();
        
        $modules = \App\Models\TrainingModule::active()->get();

        $stats = [
            'total' => AiKnowledgeSource::count(),
            'active' => AiKnowledgeSource::where('status', 'active')->count(),
            'processing' => AiKnowledgeSource::whereIn('status', ['processing', 'uploaded'])->count(),
            'ready' => AiKnowledgeSource::whereIn('status', ['ready', 'indexed'])->count(),
            'failed' => AiKnowledgeSource::where('status', 'failed')->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'sources' => $sources,
                'stats' => $stats,
                'modules' => $modules
            ]);
        }
            
        return view('admin.ai.sources.index', compact('sources', 'modules', 'stats'));
    }

    public function create()
    {
        $modules = \App\Models\TrainingModule::active()->get();
        return view('admin.ai.sources.create', compact('modules'));
    }

    public function store(StoreAiKnowledgeSourceRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        $data['uploaded_by'] = Auth::id();
        $data['status'] = \App\Enums\AiSourceStatus::UPLOADED;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('knowledge_sources', 'public');
            $data['file_path'] = $path;
        }

        $source = AiKnowledgeSource::create($data);

        // Trigger ingestion
        try {
            $this->ingestionService->processSource($source);
        } catch (\Exception $e) {
            \Log::error("Ingestion trigger failed: " . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Source created and processing started.',
                'source' => $source
            ], 201);
        }

        return redirect()->route('admin.ai.sources.index')
            ->with('success', 'Knowledge source uploaded and processing initiated.');
    }

    public function show(AiKnowledgeSource $source)
    {
        $source->load('module', 'uploader', 'chunks');
        return response()->json($source);
    }

    public function update(UpdateAiKnowledgeSourceRequest $request, AiKnowledgeSource $source)
    {
        $data = $request->validated();
        
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('knowledge_sources');
            $data['file_path'] = $path;
        }

        $source->update($data);

        return response()->json([
            'message' => 'Source updated.',
            'source' => $source
        ]);
    }

    public function reprocess(AiKnowledgeSource $source)
    {
        $this->ingestionService->processSource($source);
        
        return response()->json([
            'message' => 'Reprocessing started.',
            'source' => $source
        ]);
    }

    public function toggleActive(AiKnowledgeSource $source)
    {
        $source->update(['is_active' => !$source->is_active]);
        
        return response()->json([
            'message' => 'Status toggled.',
            'is_active' => $source->is_active
        ]);
    }

    public function destroy(AiKnowledgeSource $source)
    {
        $source->delete();
        return response()->json(['message' => 'Source deleted.']);
    }
}
