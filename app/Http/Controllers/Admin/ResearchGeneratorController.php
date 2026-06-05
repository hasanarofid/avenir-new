<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ResearchProject;
use App\Models\ResearchDocument;
use App\Jobs\ExtractDocumentJob;
use App\Jobs\GenerateResearchDraftJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ResearchGeneratorController extends Controller
{
    public function index()
    {
        $projects = ResearchProject::with('creator')->latest()->paginate(10);
        return Inertia::render('Admin/ResearchGenerator/Index', ['projects' => $projects]);
    }

    public function create()
    {
        return Inertia::render('Admin/ResearchGenerator/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticker' => 'required|string|max:10',
            'title' => 'required|string|max:255',
            'prompt' => 'nullable|string',
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:pdf|max:20480', // 20MB limit
        ]);

        $project = ResearchProject::create([
            'ticker' => $validated['ticker'],
            'title' => $validated['title'],
            'prompt' => $validated['prompt'],
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('research_documents', 'public');
                
                $doc = ResearchDocument::create([
                    'project_id' => $project->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                ]);

                // Dispatch extraction job
                ExtractDocumentJob::dispatch($doc);
            }
        }

        return redirect()->route('admin.research-generator.show', $project->id)
            ->with('success', 'Project created and documents are being extracted.');
    }

    public function show(ResearchProject $project)
    {
        $project->load('documents', 'drafts');
        return Inertia::render('Admin/ResearchGenerator/Show', ['project' => $project]);
    }

    public function generate(Request $request, ResearchProject $project)
    {
        $modelType = $request->input('model_type', 'default');
        
        GenerateResearchDraftJob::dispatch($project, $modelType);

        return back()->with('success', 'Generation job dispatched.');
    }
}
