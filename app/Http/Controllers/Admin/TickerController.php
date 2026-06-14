<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticker;
use App\Models\Article;
use App\Models\Disclosure;
use App\Services\ChatGptService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TickerController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticker::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('company_name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
        }

        $emitens = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Tickers/Index', [
            'emitens' => $emitens,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        $articles = Article::latest()->get(['id', 'title']);
        $disclosures = Disclosure::latest()->get(['id', 'title']);
        
        return Inertia::render('Admin/Tickers/CreateEdit', [
            'articles' => $articles,
            'disclosures' => $disclosures,
            'ticker' => null
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:10|unique:tickers',
            'company_name' => 'required|string|max:255',
            'sector' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'current_price' => 'nullable|numeric',
            'target_price' => 'nullable|numeric',
            'recommendation' => 'nullable|string|max:50',
            'sub_sektor' => 'nullable|string|max:255',
            'industri' => 'nullable|string|max:255',
            'papan_pencatatan' => 'nullable|string|max:50',
            'tanggal_listing' => 'nullable|date',
            'website' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:255',
            'business_summary' => 'nullable|string',
            'ticker_brief' => 'nullable|string',
            'risk_summary' => 'nullable|string',
            'investment_angle' => 'nullable|string',
            'company_profile' => 'nullable|array',
            'financial_highlights' => 'nullable|array',
            'financial_ratios' => 'nullable|array',
            'main_risks' => 'nullable|array',
            'business_segments' => 'nullable|array',
            'competitive_advantage' => 'nullable|array',
            'key_risks' => 'nullable|array',
            'status' => 'nullable|string',
            'article_ids' => 'nullable|array',
            'disclosure_ids' => 'nullable|array',
        ]);

        $tickerData = $validated;
        unset($tickerData['article_ids'], $tickerData['disclosure_ids']);

        $ticker = Ticker::create($tickerData);

        if ($request->has('article_ids')) {
            $ticker->articles()->sync($request->article_ids);
        }
        
        if ($request->has('disclosure_ids')) {
            Disclosure::whereIn('id', $request->disclosure_ids)->update(['ticker_id' => $ticker->id]);
        }

        return redirect()->route('admin.emitens.index')->with('success', 'Emiten created successfully.');
    }

    public function edit(Ticker $emiten)
    {
        $emiten->load('articles', 'disclosures');
        $articles = Article::latest()->get(['id', 'title']);
        $disclosures = Disclosure::latest()->get(['id', 'title']);
        
        // Format for Vue form
        $emiten->article_ids = $emiten->articles->pluck('id')->toArray();
        $emiten->disclosure_ids = $emiten->disclosures->pluck('id')->toArray();

        return Inertia::render('Admin/Tickers/CreateEdit', [
            'ticker' => $emiten,
            'articles' => $articles,
            'disclosures' => $disclosures
        ]);
    }

    public function update(Request $request, Ticker $emiten)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:10|unique:tickers,symbol,' . $emiten->id,
            'company_name' => 'required|string|max:255',
            'sector' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'current_price' => 'nullable|numeric',
            'target_price' => 'nullable|numeric',
            'recommendation' => 'nullable|string|max:50',
            'sub_sektor' => 'nullable|string|max:255',
            'industri' => 'nullable|string|max:255',
            'papan_pencatatan' => 'nullable|string|max:50',
            'tanggal_listing' => 'nullable|date',
            'website' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:255',
            'business_summary' => 'nullable|string',
            'ticker_brief' => 'nullable|string',
            'risk_summary' => 'nullable|string',
            'investment_angle' => 'nullable|string',
            'company_profile' => 'nullable|array',
            'financial_highlights' => 'nullable|array',
            'financial_ratios' => 'nullable|array',
            'main_risks' => 'nullable|array',
            'business_segments' => 'nullable|array',
            'competitive_advantage' => 'nullable|array',
            'key_risks' => 'nullable|array',
            'status' => 'nullable|string',
            'article_ids' => 'nullable|array',
            'disclosure_ids' => 'nullable|array',
        ]);

        $tickerData = $validated;
        unset($tickerData['article_ids'], $tickerData['disclosure_ids']);

        $emiten->update($tickerData);

        if ($request->has('article_ids')) {
            $emiten->articles()->sync($request->article_ids);
        }

        if ($request->has('disclosure_ids')) {
            // First detach any previously attached that are not in the current list
            Disclosure::where('ticker_id', $emiten->id)
                ->whereNotIn('id', $request->disclosure_ids ?? [])
                ->update(['ticker_id' => null]);
                
            // Attach the new ones
            if (!empty($request->disclosure_ids)) {
                Disclosure::whereIn('id', $request->disclosure_ids)->update(['ticker_id' => $emiten->id]);
            }
        } else {
             Disclosure::where('ticker_id', $emiten->id)->update(['ticker_id' => null]);
        }

        return redirect()->route('admin.emitens.index')->with('success', 'Emiten updated successfully.');
    }

    public function destroy(Ticker $emiten)
    {
        $emiten->delete();
        return redirect()->route('admin.emitens.index')->with('success', 'Emiten deleted successfully.');
    }

    public function generateWithAI(Request $request, ChatGptService $aiService)
    {
        $request->validate([
            'symbol' => 'required|string',
            'company_name' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:20480',
            'pdf_base64' => 'nullable|string',
        ]);

        $symbol = $request->symbol;
        $companyName = $request->company_name ?? $symbol;
        $pdfText = "";

        if ($request->has('pdf_base64') && $request->input('pdf_base64')) {
            try {
                $base64 = $request->input('pdf_base64');
                if (strpos($base64, ',') !== false) {
                    $base64 = explode(',', $base64)[1];
                }
                $pdfContent = base64_decode($base64);
                
                $tempPath = sys_get_temp_dir() . '/' . uniqid('pdf_') . '.pdf';
                file_put_contents($tempPath, $pdfContent);

                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($tempPath);
                $pdfText = $pdf->getText();
                // Truncate to avoid massive payload
                $pdfText = substr($pdfText, 0, 80000);
                
                unlink($tempPath);
            } catch (\Exception $e) {
                \Log::error("PDF Base64 Parse error: " . $e->getMessage());
            }
        } elseif ($request->hasFile('pdf_file')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($request->file('pdf_file')->path());
                $pdfText = $pdf->getText();
                // Truncate to avoid massive payload (approx 80,000 chars ~ 20k tokens)
                $pdfText = substr($pdfText, 0, 80000); 
            } catch (\Exception $e) {
                \Log::error("PDF Parse error: " . $e->getMessage());
            }
        }

        $systemPrompt = "You are a professional Equity Research Analyst focused on the Indonesian stock market (IDX). " .
            "Provide comprehensive profile and financial estimates for the given company in JSON format exactly matching the schema provided. " .
            "Format the monetary values in Indonesian Rupiah (e.g. 'Rp 100,5 T', 'Rp 15,2 T') and percentages with commas (e.g. '12,5%'). " .
            "CRITICAL: If source document text is provided, extract the data STRICTLY from the text. Do not hallucinate or guess numbers. If a specific metric is not found in the text, return null or an empty string.";

        $userPrompt = "Generate data for Ticker: {$symbol} (Company: {$companyName}). Return a JSON object with this exact structure:
{
  \"company_name\": \"Full legal name of the company\",
  \"description\": \"A professional summary of the company's main business, market position, and strengths. (1-2 paragraphs)\",
  \"sector\": \"The company's primary sector (e.g., Financials, Energy, Consumer Goods)\",
  \"sub_sector\": \"The company's sub-sector\",
  \"industry\": \"Industry name\",
  \"company_profile\": {
    \"board\": \"Utama or Pengembangan\",
    \"listingDate\": \"e.g., 10 November 2003\",
    \"website\": \"Company website URL\",
    \"business\": \"Short description of business activities\",
    \"marketCap\": \"e.g., Rp 600 T\",
    \"outstandingShares\": \"e.g., 150 Miliar\",
    \"address\": \"Company headquarters address\",
    \"phone\": \"Company phone number\",
    \"email\": \"Corporate secretary email\",
    \"tags\": [\"Array\", \"of\", \"3-4\", \"keywords\", \"like\", \"Bluechip\", \"SOE\"]
  },
  \"main_risks\": [
    \"Array of 4-5 main business or macroeconomic risks facing the company\"
  ],
  \"financial_highlights\": [
    { \"title\": \"Net Profit\", \"value\": \"Rp X T\", \"change\": \"+Y%\", \"type\": \"up\", \"icon\": \"TrendingUp\" },
    { \"title\": \"Total Assets\", \"value\": \"Rp Z T\", \"change\": \"+W%\", \"type\": \"up\", \"icon\": \"Building2\" }
    // Generate 4-6 realistic highlight metrics relevant to their sector
  ],
  \"financial_ratios\": [
    { \"name\": \"PER\", \"value\": \"XX,Xx\", \"period\": \"TTM\", \"change\": \"+Y%\" },
    { \"name\": \"PBV\", \"value\": \"X,Xx\", \"period\": \"TTM\", \"change\": \"-Z%\" },
    { \"name\": \"ROE\", \"value\": \"XX,X%\", \"period\": \"TTM\", \"change\": \"+W%\" }
  ]
}";

        if (!empty($pdfText)) {
            $userPrompt .= "\n\n=== SOURCE DOCUMENT TEXT ===\n" . $pdfText;
        }

        $result = $aiService->generateStructuredJson($systemPrompt, $userPrompt);

        if (!$result || empty($result['structured_json'])) {
            // Fallback to OpenRouter if ChatGPT fails (e.g. quota limit)
            $openRouter = app(\App\Services\OpenRouterService::class);
            $result = $openRouter->generateStructuredJson($systemPrompt, $userPrompt);
            
            if (!$result || empty($result['structured_json'])) {
                $mockData = $this->getMockAiData($symbol, $companyName);
                \Illuminate\Support\Facades\Log::info("AI generation failed, returning Mock Data: ", $mockData);
                return response()->json($mockData);
            }
        }

        \Illuminate\Support\Facades\Log::info("AI generation successful, returning Data: ", $result['structured_json']);
        return response()->json($result['structured_json']);
    }

    private function getMockAiData($symbol, $companyName)
    {
        return [
            "company_name" => $companyName !== $symbol ? $companyName : "PT " . strtoupper($symbol) . " Tbk",
            "description" => "{$companyName} ({$symbol}) adalah perusahaan terkemuka yang beroperasi di sektornya. Perusahaan ini telah mencatatkan pertumbuhan yang solid dalam beberapa tahun terakhir dan terus melakukan ekspansi bisnis untuk memperkuat posisinya di pasar modal Indonesia. Fundamental perusahaan menunjukkan kinerja yang stabil dengan manajemen operasi yang terukur.",
            "sector" => "Financials",
            "sub_sector" => "Bank",
            "industry" => "Perbankan / Finansial",
            "company_profile" => [
                "board" => "Utama",
                "listingDate" => "10 November 2003",
                "website" => "www." . strtolower($symbol) . ".co.id",
                "business" => "Menyediakan berbagai layanan jasa perbankan dan finansial.",
                "marketCap" => "Rp 500 T",
                "outstandingShares" => "100 Miliar",
                "address" => "Jl. Jend. Sudirman Kav. X, Jakarta Pusat",
                "phone" => "021-1234567",
                "email" => "corsec@" . strtolower($symbol) . ".co.id",
                "tags" => ["Bluechip", "LQ45", "Dividen"]
            ],
            "main_risks" => [
                "Fluktuasi suku bunga acuan yang dapat menekan margin.",
                "Risiko perlambatan ekonomi yang dapat meningkatkan kredit bermasalah.",
                "Perubahan regulasi kebijakan moneter dari pemerintah.",
                "Persaingan industri yang semakin ketat."
            ],
            "financial_highlights" => [
                [ "title" => "Net Profit", "value" => "Rp 15,2 T", "change" => "+10,5%", "type" => "up", "icon" => "TrendingUp" ],
                [ "title" => "Total Assets", "value" => "Rp 1.250 T", "change" => "+8,2%", "type" => "up", "icon" => "Activity" ],
                [ "title" => "Revenue", "value" => "Rp 45,2 T", "change" => "+5,1%", "type" => "up", "icon" => "TrendingUp" ],
                [ "title" => "Operating Exp", "value" => "Rp 15,3 T", "change" => "-2,1%", "type" => "down", "icon" => "TrendingDown" ]
            ],
            "financial_ratios" => [
                [ "name" => "PER", "value" => "12,5x", "period" => "TTM", "change" => "-5,2%" ],
                [ "name" => "PBV", "value" => "2,1x", "period" => "TTM", "change" => "+2,1%" ],
                [ "name" => "ROE", "value" => "18,4%", "period" => "TTM", "change" => "+1,5%" ],
                [ "name" => "ROA", "value" => "2,8%", "period" => "TTM", "change" => "-0,1%" ]
            ]
        ];
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\TickersImport, $request->file('file'));
            return redirect()->route('admin.emitens.index')->with('success', 'Data emiten berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->route('admin.emitens.index')->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}
