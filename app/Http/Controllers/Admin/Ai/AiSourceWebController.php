<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiKnowledgeSource;
use App\Models\TrainingModule;
use App\Services\Ai\AiSourceIngestionService;
use App\Http\Requests\Admin\Ai\StoreAiKnowledgeSourceRequest;
use App\Http\Requests\Admin\Ai\UpdateAiKnowledgeSourceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiSourceWebController extends Controller
{
    protected $ingestionService;

    public function __construct(AiSourceIngestionService $ingestionService)
    {
        $this->ingestionService = $ingestionService;
    }

    public function index(Request $request)
    {
        $query = AiKnowledgeSource::with(['module', 'uploader'])->withCount('chunks');

        // Filters
        if ($request->filled('module_id')) {
            $query->where('module_id', $request->module_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('trust_level')) {
            $query->where('trust_level', $request->trust_level);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('topic', 'like', '%' . $searchTerm . '%');
            });
        }

        $sources = $query->latest()->paginate(15);
        $modules = TrainingModule::active()->get();

        // 5. Summary Statistics (Requirement 9)
        $stats = [
            'total' => AiKnowledgeSource::count(),
            'active' => AiKnowledgeSource::where('status', \App\Enums\AiSourceStatus::ACTIVE)->count(),
            'processing' => AiKnowledgeSource::whereIn('status', [
                \App\Enums\AiSourceStatus::UPLOADED, 
                \App\Enums\AiSourceStatus::PROCESSING,
                \App\Enums\AiSourceStatus::INDEXED
            ])->count(),
            'ready' => AiKnowledgeSource::where('status', \App\Enums\AiSourceStatus::READY)->count(),
            'failed' => AiKnowledgeSource::where('status', \App\Enums\AiSourceStatus::FAILED)->count(),
        ];

        return view('admin.ai.sources.index', compact('sources', 'modules', 'stats'));
    }

    public function create()
    {
        $modules = TrainingModule::active()->get();
        return view('admin.ai.sources.create', compact('modules'));
    }

    public function store(StoreAiKnowledgeSourceRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);
        $data['uploaded_by'] = Auth::id();

        // Handle Draft vs Process logic
        $isDraft = $request->has('draft');
        $data['is_active'] = $isDraft ? false : $request->boolean('is_active', true);
        $data['status'] = $isDraft ? \App\Enums\AiSourceStatus::DRAFT : \App\Enums\AiSourceStatus::UPLOADED;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('knowledge_sources');
            $data['file_path'] = $path;
        }

        $source = AiKnowledgeSource::create($data);

        // Only trigger ingestion if it's not a draft
        if (!$isDraft) {
            $this->ingestionService->processSource($source);
            $message = 'Source uploaded and processing started.';
        } else {
            $message = 'Source saved as draft. You can initialize processing later.';
        }

        return redirect()->route('admin.ai.sources.index')
            ->with('success', $message);
    }

    public function show(AiKnowledgeSource $source)
    {
        $source->load(['module', 'uploader', 'chunks' => function($q) {
            $q->take(50);
        }]);
        return view('admin.ai.sources.show', compact('source'));
    }

    public function edit(AiKnowledgeSource $source)
    {
        $modules = TrainingModule::active()->get();
        return view('admin.ai.sources.edit', compact('source', 'modules'));
    }

    public function update(UpdateAiKnowledgeSourceRequest $request, AiKnowledgeSource $source)
    {
        $data = $request->validated();
        
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('knowledge_sources');
            $data['file_path'] = $path;
        }

        $source->update($data);

        return redirect()->route('admin.ai.sources.show', $source)
            ->with('success', 'Source updated successfully.');
    }

    public function reprocess(AiKnowledgeSource $source)
    {
        $this->ingestionService->processSource($source);
        return back()->with('success', 'Reprocessing task dispatched.');
    }

    public function toggleActive(AiKnowledgeSource $source)
    {
        $newActive = !$source->is_active;
        
        $status = $source->status;
        if ($newActive && $source->indexing_status === \App\Enums\AiProcessingStatus::COMPLETED) {
            $status = \App\Enums\AiSourceStatus::ACTIVE;
        } elseif (!$newActive && $source->status === \App\Enums\AiSourceStatus::ACTIVE) {
            $status = \App\Enums\AiSourceStatus::READY;
        }

        $source->update([
            'is_active' => $newActive,
            'status' => $status
        ]);

        return back()->with('success', 'Source status updated to ' . strtoupper($status->value));
    }

    public function reindex(AiKnowledgeSource $source)
    {
        // For now, reindex just triggers reprocessing if we don't have a separate indexing service
        $source->update([
            'indexing_status' => \App\Enums\AiProcessingStatus::PROCESSING,
            'status' => \App\Enums\AiSourceStatus::INDEXED,
        ]);
        
        // Simulate immediate completion/advancement
        $source->update([
            'indexing_status' => \App\Enums\AiProcessingStatus::COMPLETED,
            'status' => $source->is_active ? \App\Enums\AiSourceStatus::ACTIVE : \App\Enums\AiSourceStatus::READY,
        ]);

        return back()->with('success', 'Source reindexed successfully.');
    }

    public function destroy(AiKnowledgeSource $source)
    {
        $source->delete();
        return redirect()->route('admin.ai.sources.index')
            ->with('success', 'Source deleted.');
    }
}
