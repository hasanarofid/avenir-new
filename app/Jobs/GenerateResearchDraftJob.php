<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\ResearchProject;
use App\Models\ResearchDraft;
use App\Services\OpenRouterService;
use Illuminate\Support\Facades\Log;

class GenerateResearchDraftJob implements ShouldQueue
{
    use Queueable;

    protected ResearchProject $project;
    protected string $modelType; // 'default' or 'fallback' or specific model

    public function __construct(ResearchProject $project, string $modelType = 'default')
    {
        $this->project = $project;
        $this->modelType = $modelType;
    }

    public function handle(OpenRouterService $openRouter): void
    {
        try {
            // 1. Gather context
            $this->project->update(['status' => 'generating']);
            
            $documents = $this->project->documents()->get();
            $contextText = "";
            foreach ($documents as $doc) {
                $contextText .= "\n--- Document: {$doc->file_name} ---\n";
                $contextText .= $doc->extracted_text ?? '';
            }

            // 2. Prepare Prompts
            $systemPrompt = "Anda adalah analis ekuitas senior dengan 15 tahun pengalaman di pasar modal Indonesia. Tugas Anda adalah menulis riset ekuitas profesional berdasarkan data keuangan yang diberikan. Output HARUS berupa JSON valid tanpa markdown block ```json. Struktur JSON harus memiliki key: ticker, rating (BUY/HOLD/SELL), target_price, subtitle (1-2 kalimat), revenue (format ringkas misal 'Rp5,47T'), patmi, sales, tags (dipisah spasi), dan sections (berisi object dengan key: executive_summary, financial_highlights, key_risks, conclusion).";
            
            $userPrompt = "Berikut adalah data untuk saham {$this->project->ticker} - {$this->project->title}.\n\n";
            if ($this->project->prompt) {
                $userPrompt .= "Instruksi Khusus: {$this->project->prompt}\n\n";
            }
            $userPrompt .= "=== DATA DOKUMEN ===\n" . mb_substr($contextText, 0, 100000); // Limit context if too large

            // 3. Call OpenRouter
            $model = $this->modelType === 'default' 
                ? config('services.openrouter.default_model')
                : config('services.openrouter.fallback_model');

            $result = $openRouter->generateStructuredJson($systemPrompt, $userPrompt, ['model' => $model]);

            if ($result) {
                // Log AI Usage
                \App\Models\AILog::create([
                    'feature' => 'ResearchGenerator',
                    'input_hash' => hash('sha256', $systemPrompt . $userPrompt),
                    'output' => is_string($result) ? $result : json_encode($result),
                    'model' => $model,
                    'sources' => $documents->pluck('file_path')->toArray(),
                    // Cannot easily get auth()->id() inside a Job reliably unless passed, we'll leave reviewer_id null or get from project
                    'reviewer_id' => $this->project->created_by,
                ]);

                // 4. Save Draft
                ResearchDraft::create([
                    'project_id' => $this->project->id,
                    'model_used' => $result['model_used'],
                    'structured_json' => $result['structured_json'],
                    'status' => 'draft'
                ]);

                $this->project->update(['status' => 'review']);
                Log::info("Research draft generated for project: {$this->project->id}");
            } else {
                $this->project->update(['status' => 'draft']); // revert
                Log::error("Failed to generate research draft for project: {$this->project->id} - No result from OpenRouter.");
            }

        } catch (\Exception $e) {
            $this->project->update(['status' => 'draft']);
            Log::error("Exception in GenerateResearchDraftJob for project {$this->project->id}: " . $e->getMessage());
        }
    }
}
