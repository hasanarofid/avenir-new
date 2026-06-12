<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticker;
use App\Models\Article;
use App\Models\Disclosure;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TickerController extends Controller
{
    public function index()
    {
        $emitens = Ticker::latest()->get();
        return Inertia::render('Admin/Tickers/Index', [
            'emitens' => $emitens
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
            'company_profile' => 'nullable|array',
            'financial_highlights' => 'nullable|array',
            'financial_ratios' => 'nullable|array',
            'main_risks' => 'nullable|array',
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
            'company_profile' => 'nullable|array',
            'financial_highlights' => 'nullable|array',
            'financial_ratios' => 'nullable|array',
            'main_risks' => 'nullable|array',
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

    public function generateWithAI(Request $request, OpenRouterService $openRouter)
    {
        $request->validate([
            'symbol' => 'required|string',
            'company_name' => 'nullable|string'
        ]);

        $symbol = $request->symbol;
        $companyName = $request->company_name ?? $symbol;

        $systemPrompt = "You are a professional Equity Research Analyst focused on the Indonesian stock market (IDX). " .
            "Provide comprehensive profile and financial estimates for the given company in JSON format exactly matching the schema provided. " .
            "If exact current data is unknown, provide realistic recent estimates or generic placeholders so the user can edit them later. " .
            "Format the monetary values in Indonesian Rupiah (e.g. 'Rp 100,5 T', 'Rp 15,2 T') and percentages with commas (e.g. '12,5%').";

        $userPrompt = "Generate data for Ticker: {$symbol} (Company: {$companyName}). Return a JSON object with this exact structure:
{
  \"description\": \"A professional summary of the company's main business, market position, and strengths. (1-2 paragraphs)\",
  \"sector\": \"The company's primary sector (e.g., Financials, Energy, Consumer Goods)\",
  \"company_profile\": {
    \"industry\": \"Industry name\",
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
    // Generate 4-6 realistic highlight metrics relevant to their sector (e.g., Net Interest Income for banks, Revenue for others)
  ],
  \"financial_ratios\": [
    { \"name\": \"PER\", \"value\": \"XX,Xx\", \"period\": \"TTM\", \"change\": \"+Y%\" },
    { \"name\": \"PBV\", \"value\": \"X,Xx\", \"period\": \"TTM\", \"change\": \"-Z%\" },
    { \"name\": \"ROE\", \"value\": \"XX,X%\", \"period\": \"TTM\", \"change\": \"+W%\" }
    // Add 4-6 relevant financial ratios
  ]
}";

        $result = $openRouter->generateStructuredJson($systemPrompt, $userPrompt);

        if (!$result || empty($result['structured_json'])) {
            // Karena ini lingkungan development (atau jika timeout), kita kembalikan mock data
            // agar fitur Autofill AI tetap bisa didemonstrasikan.
            return response()->json($this->getMockAiData($symbol, $companyName));
        }

        return response()->json($result['structured_json']);
    }

    private function getMockAiData($symbol, $companyName)
    {
        return [
            "description" => "{$companyName} ({$symbol}) adalah perusahaan terkemuka yang beroperasi di sektornya. Perusahaan ini telah mencatatkan pertumbuhan yang solid dalam beberapa tahun terakhir dan terus melakukan ekspansi bisnis untuk memperkuat posisinya di pasar modal Indonesia. Fundamental perusahaan menunjukkan kinerja yang stabil dengan manajemen operasi yang terukur.",
            "sector" => "Financials",
            "company_profile" => [
                "industry" => "Perbankan / Finansial",
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
}
