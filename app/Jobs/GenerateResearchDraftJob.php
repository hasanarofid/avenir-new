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
            $this->project->update(['status' => 'generating']);
            
            $documents = $this->project->documents()->get();
            $contextText = "";
            foreach ($documents as $doc) {
                $contextText .= "\n--- Document: {$doc->file_name} ---\n";
                $contextText .= $doc->extracted_text ?? '';
            }

            // Pick Model
            $model = $this->modelType === 'default' 
                ? config('services.openrouter.default_model', 'anthropic/claude-3.5-sonnet')
                : config('services.openrouter.fallback_model', 'openai/gpt-4o');

            // ==========================================
            // STEP 1: Fact Extraction (Prompt 1)
            // ==========================================
            $systemPrompt1 = "Anda adalah analis ekstraksi dokumen untuk AVENIR Research. Tugas Anda adalah mengekstrak fakta penting dari dokumen yang diunggah user tanpa membuat opini, interpretasi, analisis, atau rekomendasi investasi. Ambil hanya informasi yang eksplisit tersedia. Jika informasi tidak tersedia, tulis 'Tidak tersedia'. Output HARUS berupa JSON objek valid.
Struktur JSON wajib memiliki key:
- ticker
- company_name
- document_type
- document_date
- document_source
- reporting_period
- key_facts (array of strings, 5-10 bullets)
- parties_involved (array of strings)
- transaction_value
- transaction_price_or_tender_offer
- effective_date_or_schedule
- transaction_purpose
- financial_numbers (object dengan key: revenue, net_profit, assets, liabilities, equity, eps, bvps, financial_ratios, transaction_value_to_equity_or_assets)
- risks_mentioned (object dengan key: explicit_risks, execution_risk, regulatory_risk, funding_risk, affiliated_transaction_risk)
- data_gaps (array of strings)
- evidence_map (array of strings)";

            $userPrompt1 = "Berikut adalah teks dokumen untuk saham {$this->project->ticker} - {$this->project->title}.\n\n=== DATA DOKUMEN ===\n" . mb_substr($contextText, 0, 100000);

            Log::info("Running Step 1: Fact Extraction for Project {$this->project->id}");
            $res1 = $openRouter->generateStructuredJson($systemPrompt1, $userPrompt1, ['model' => $model]);
            
            if (!$res1 || empty($res1['structured_json'])) {
                throw new \Exception("Step 1 (Fact Extraction) failed to return valid JSON.");
            }

            $extractedFacts = $res1['structured_json'];

            // ==========================================
            // STEP 2: Equity Research Generator (Prompt 2)
            // ==========================================
            $systemPrompt2 = "Anda adalah Equity Research Analyst untuk AVENIR Research. Tugas Anda menyusun draft riset saham profesional, objektif, dan berbasis data berdasarkan hasil ekstraksi fakta dokumen dan data tambahan yang disediakan.
Aturan penting:
- Jangan memberikan target price jika data valuasi tidak cukup. Jika target price tidak dicantumkan, isi target_price dengan null.
- Jika target price dibuat, jelaskan metode, asumsi, dan keterbatasannya.
- Jika data tidak tersedia, tulis 'data belum tersedia' atau 'tidak tersedia'.
- Hindari bahasa promosi, pom-pom, atau ajakan beli/jual.
- Rating harus dapat dijelaskan oleh tesis, valuasi, dan risiko.
Rating policy: BUY (upside menarik, katalis kuat), HOLD (risk-reward seimbang/data kurang), SELL (downside dominan, risiko fundamental tinggi), NEUTRAL (dokumen rutin administratif/dampak belum jelas).

Output HARUS berupa JSON objek valid.
Struktur JSON wajib memiliki key:
- ticker
- company_name
- sector
- document_type
- analysis_date (format YYYY-MM-DD)
- rating
- target_price (angka atau null)
- target_price_note
- current_price (angka atau null)
- upside_downside (misal '+15.5%' atau null)
- risk_level ('Low' / 'Medium' / 'High')
- conviction_level ('Low' / 'Medium' / 'High')
- time_horizon
- title
- executive_summary (array of strings)
- event_overview
- business_impact
- financial_impact
- valuation_view (object dengan key: method, assumptions (array of strings), limitations (array of strings))
- scenario_analysis (object dengan key: bull_case, base_case, bear_case)
- key_catalysts (array of strings)
- key_risks (array of strings)
- red_flags (array of strings)
- data_gaps (array of strings)
- analyst_conclusion
- disclaimer";

            $userPrompt2 = "Berikut adalah hasil ekstraksi fakta dari dokumen emiten:\n" . json_encode($extractedFacts, JSON_PRETTY_PRINT) . "\n\n";
            if ($this->project->prompt) {
                $userPrompt2 .= "Instruksi Khusus dari user: {$this->project->prompt}\n\n";
            }
            $userPrompt2 .= "Susunlah draft riset ekuitas lengkap berdasarkan fakta-fakta di atas.";

            Log::info("Running Step 2: Research Generation for Project {$this->project->id}");
            $res2 = $openRouter->generateStructuredJson($systemPrompt2, $userPrompt2, ['model' => $model]);
            
            if (!$res2 || empty($res2['structured_json'])) {
                throw new \Exception("Step 2 (Research Generation) failed to return valid JSON.");
            }

            $researchDraft = $res2['structured_json'];

            // ==========================================
            // STEP 3: Quality Control & Hallucination Check (Prompt 3)
            // ==========================================
            $systemPrompt3 = "Anda adalah editor quality control AVENIR Research. Tugas Anda menilai draft riset AI sebelum dipublikasikan. Periksa apakah setiap klaim penting didukum oleh dokumen fakta.
Aturan:
- Flag semua angka yang tidak punya sumber.
- Flag target price yang tidak menjelaskan metode.
- Flag rating BUY/SELL jika hanya berdasarkan sentimen tanpa data.
- Flag kalimat yang terdengar seperti rekomendasi beli/jual langsung.

Output HARUS berupa JSON objek valid dengan key:
- quality_score (angka 0-100)
- model_confidence ('Low' / 'Medium' / 'High')
- unsupported_claims (array of strings)
- overconfident_statements (array of strings)
- missing_evidence (array of strings)
- valuation_weakness
- rating_justification_check
- target_price_check
- required_revision (array of strings)
- publish_recommendation ('Approve' / 'Revise' / 'Reject')";

            $userPrompt3 = "Data acuan ekstraksi fakta:\n" . json_encode($extractedFacts, JSON_PRETTY_PRINT) . "\n\n";
            $userPrompt3 .= "Draft riset yang dihasilkan:\n" . json_encode($researchDraft, JSON_PRETTY_PRINT) . "\n\n";
            $userPrompt3 .= "Lakukan evaluasi QC dan deteksi halusinasi pada draft riset tersebut.";

            Log::info("Running Step 3: Quality Control for Project {$this->project->id}");
            $res3 = $openRouter->generateStructuredJson($systemPrompt3, $userPrompt3, ['model' => $model]);
            
            if (!$res3 || empty($res3['structured_json'])) {
                throw new \Exception("Step 3 (Quality Control) failed to return valid JSON.");
            }

            $qcReport = $res3['structured_json'];

            // ==========================================
            // Combine Results
            // ==========================================
            $finalStructuredJson = $researchDraft;
            
            // Add QC details
            $finalStructuredJson['quality_score'] = intval($qcReport['quality_score'] ?? 80);
            $finalStructuredJson['confidence_level'] = $qcReport['model_confidence'] ?? 'Medium';
            $finalStructuredJson['unsupported_claims'] = $qcReport['unsupported_claims'] ?? [];
            $finalStructuredJson['overconfident_statements'] = $qcReport['overconfident_statements'] ?? [];
            $finalStructuredJson['required_revision'] = $qcReport['required_revision'] ?? [];
            $finalStructuredJson['publish_recommendation'] = $qcReport['publish_recommendation'] ?? 'Revise';
            
            // Add extraction reference details for the Sources tab
            $finalStructuredJson['extracted_facts'] = $extractedFacts['key_facts'] ?? [];
            $finalStructuredJson['evidence_map'] = $extractedFacts['evidence_map'] ?? [];

            // Add backward compatibility fields or fallback array fields
            if (isset($researchDraft['valuation_view']['assumptions'])) {
                $finalStructuredJson['valuation_assumptions'] = $researchDraft['valuation_view']['assumptions'];
            } else {
                $finalStructuredJson['valuation_assumptions'] = [];
            }

            // Save Draft to Database
            ResearchDraft::create([
                'project_id' => $this->project->id,
                'model_used' => $res2['model_used'] ?? $model,
                'structured_json' => $finalStructuredJson,
                'status' => 'draft'
            ]);

            // Log AI audit info
            \App\Models\AILog::create([
                'feature' => 'ResearchGenerator',
                'input_hash' => hash('sha256', $contextText),
                'output' => json_encode($finalStructuredJson),
                'model' => $model,
                'sources' => $documents->pluck('file_path')->toArray(),
                'reviewer_id' => $this->project->created_by,
            ]);

            $this->project->update(['status' => 'review']);
            Log::info("Research draft generated completely with 3-stage pipeline for project: {$this->project->id}");

        } catch (\Exception $e) {
            $this->project->update(['status' => 'draft']);
            Log::error("Exception in 3-stage GenerateResearchDraftJob for project {$this->project->id}: " . $e->getMessage());
        }
    }
}
