<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EducationContent;
use App\Models\TrainingModule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of document resources.
     */
    public function index()
    {
        $contents = EducationContent::where('type', 'document')
            ->with('trainingModule')
            ->latest()
            ->get();
            
        return view('admin.documents.index', compact('contents'));
    }

    /**
     * Show form to create new document.
     */
    public function create()
    {
        $modules = TrainingModule::all();
        return view('admin.documents.create', compact('modules'));
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'training_module_id' => 'required|exists:training_modules,id',
            'category' => 'required|string',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'short_summary' => 'nullable|string|max:255',
            'prerequisites' => 'nullable|string',
            'related_materials' => 'nullable|string',
            'ai_context' => 'nullable|string',
            'related_topic' => 'nullable|string|max:255',
            'source_type' => 'required|in:upload,external',
            'external_url' => 'required_if:source_type,external|nullable|url',
            'document_file' => 'required_if:source_type,upload|nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'pages_count' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->except(['document_file', 'thumbnail', 'external_url']);
        $data['type'] = 'Document';
        $data['code'] = 'EDU-DOC-' . strtoupper(Str::random(5));
        $data['slug'] = Str::slug($request->title) . '-' . Str::random(4);

        // Handle File URL source
        if ($request->source_type === 'external') {
            $data['file_url'] = $request->external_url;
            $data['file_type'] = pathinfo($request->external_url, PATHINFO_EXTENSION) ?: 'URL';
        } elseif ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/documents', $fileName);
            $data['file_url'] = Storage::url($path);
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        // Handle Thumbnail Upload
        if ($request->hasFile('thumbnail')) {
            $thumb = $request->file('thumbnail');
            $thumbName = time() . '_thumb_' . Str::slug($request->title) . '.' . $thumb->getClientOriginalExtension();
            $thumbPath = $thumb->storeAs('public/thumbnails/documents', $thumbName);
            $data['thumbnail_url'] = Storage::url($thumbPath);
        }

        EducationContent::create($data);

        return redirect()->route('admin.documents.index')->with('success', 'Educational document has been added successfully.');
    }

    /**
     * Show form to edit document.
     */
    public function edit(EducationContent $document)
    {
        $modules = TrainingModule::all();
        return view('admin.documents.edit', compact('document', 'modules'));
    }

    /**
     * Update existing document.
     */
    public function update(Request $request, EducationContent $document)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'training_module_id' => 'required|exists:training_modules,id',
            'category' => 'required|string',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            'short_summary' => 'nullable|string|max:255',
            'prerequisites' => 'nullable|string',
            'related_materials' => 'nullable|string',
            'ai_context' => 'nullable|string',
            'related_topic' => 'nullable|string|max:255',
            'source_type' => 'required|in:upload,external',
            'external_url' => 'required_if:source_type,external|nullable|url',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'pages_count' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->except(['document_file', 'thumbnail', 'external_url']);

        // Handle File URL source change
        if ($request->source_type === 'external') {
            // If switching from upload to external, we might want to delete the old file
            if ($document->source_type === 'upload' && $document->file_url) {
                $oldPath = str_replace('/storage/', 'public/', $document->file_url);
                Storage::delete($oldPath);
            }
            $data['file_url'] = $request->external_url;
            $data['file_type'] = pathinfo($request->external_url, PATHINFO_EXTENSION) ?: 'URL';
        } elseif ($request->hasFile('document_file')) {
            // Delete old file if exists (whether it was an upload or an external link record)
            if ($document->source_type === 'upload' && $document->file_url) {
                $oldPath = str_replace('/storage/', 'public/', $document->file_url);
                Storage::delete($oldPath);
            }

            $file = $request->file('document_file');
            $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/documents', $fileName);
            $data['file_url'] = Storage::url($path);
            $data['file_type'] = $file->getClientOriginalExtension();
        }

        // Handle Thumbnail Update
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($document->thumbnail_url) {
                $oldThumbPath = str_replace('/storage/', 'public/', $document->thumbnail_url);
                Storage::delete($oldThumbPath);
            }

            $thumb = $request->file('thumbnail');
            $thumbName = time() . '_thumb_' . Str::slug($request->title) . '.' . $thumb->getClientOriginalExtension();
            $thumbPath = $thumb->storeAs('public/thumbnails/documents', $thumbName);
            $data['thumbnail_url'] = Storage::url($thumbPath);
        }

        $document->update($data);

        return redirect()->route('admin.documents.index')->with('success', 'Document content updated successfully.');
    }

    /**
     * Remove document.
     */
    public function destroy(EducationContent $document)
    {
        // Delete files
        if ($document->file_url) {
            $filePath = str_replace('/storage/', 'public/', $document->file_url);
            Storage::delete($filePath);
        }
        if ($document->thumbnail_url) {
            $thumbPath = str_replace('/storage/', 'public/', $document->thumbnail_url);
            Storage::delete($thumbPath);
        }

        $document->delete();
        return redirect()->route('admin.documents.index')->with('success', 'Document content has been removed.');
    }

    /**
     * Toggle document status.
     */
    public function toggleStatus(EducationContent $document)
    {
        $document->update(['is_active' => !$document->is_active]);
        $status = $document->is_active ? 'published' : 'unpublished';
        return back()->with('success', "Document is now {$status}.");
    }
}
