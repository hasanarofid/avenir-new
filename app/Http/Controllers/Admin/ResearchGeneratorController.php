<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $project->update(['status' => 'generating']);
        
        GenerateResearchDraftJob::dispatch($project, $modelType);

        return back()->with('success', 'Generation job dispatched.');
    }

    public function publishToKatalog(Request $request, ResearchProject $project)
    {
        $draft = $project->drafts()->latest()->first();
        if (!$draft) {
            return back()->withErrors(['error' => 'Draft tidak ditemukan.']);
        }

        $data = is_string($draft->structured_json) ? json_decode($draft->structured_json, true) : $draft->structured_json;
        if (!$data) {
            return back()->withErrors(['error' => 'Format JSON tidak valid.']);
        }

        // Build HTML content for Katalog
        $content = '';
        if (isset($data['sections'])) {
            $sections = $data['sections'];
            
            if (isset($sections['executive_summary']['content'])) {
                $content .= '<div class="art-body"><h3>Executive Summary</h3><p>' . $sections['executive_summary']['content'] . '</p></div>';
            }
            if (isset($sections['financial_highlights']['content'])) {
                $content .= '<div class="art-body"><h3>Financial Highlights</h3><p>' . $sections['financial_highlights']['content'] . '</p></div>';
            }
            if (isset($sections['key_risks']['content'])) {
                $content .= '<div class="art-poin"><div style="flex-shrink:0"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div><div><h4 style="color:#ef4444;margin:0 0 5px;font-size:14px">Key Risks</h4><p style="margin:0;font-size:13px;color:#94a3b8">' . $sections['key_risks']['content'] . '</p></div></div>';
            }
            if (isset($sections['conclusion']['content'])) {
                $content .= '<div class="art-body"><h3>Conclusion</h3><p>' . $sections['conclusion']['content'] . '</p></div>';
            }
        }

        // Generate a random placeholder image
        $images = [
            'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=800&q=75&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?w=800&q=75&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1579621970588-a3f5ce599827?w=800&q=75&auto=format&fit=crop'
        ];
        $randomImage = $images[array_rand($images)];

        // Create Research entry (Katalog)
        \App\Models\Research::create([
            'title' => $project->title,
            'slug' => \Illuminate\Support\Str::slug($project->title . '-' . uniqid()),
            'ticker' => $data['ticker'] ?? $project->ticker,
            'sector' => 'TBA', // Adjust based on your needs
            'subtitle' => $data['subtitle'] ?? 'Automated Research Report generated by Avenir AI.',
            'revenue' => $data['revenue'] ?? 'N/A',
            'patmi' => $data['patmi'] ?? 'N/A',
            'sales' => $data['sales'] ?? 'N/A',
            'tags' => $data['tags'] ?? 'AI Research',
            'date' => now()->format('Y-m-d'),
            'price' => $data['target_price'] ?? '0',
            'content' => $content,
            'image' => $randomImage
        ]);

        $project->update(['status' => 'published']);

        return back()->with('success', 'Riset berhasil diterbitkan ke Katalog!');
    }
}
