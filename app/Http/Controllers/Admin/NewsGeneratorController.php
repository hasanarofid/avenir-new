<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use App\Models\Article;
use Illuminate\Support\Str;

class NewsGeneratorController extends Controller
{
    public function index()
    {
        $tickers = \App\Models\Ticker::orderBy('symbol')->get(['id', 'symbol', 'company_name']);
        return Inertia::render('Admin/NewsGenerator/Index', [
            'tickers' => $tickers
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'source_type' => 'required|in:url,manual',
            'url' => 'required_if:source_type,url|nullable|url',
            'manual_text' => 'required_if:source_type,manual|nullable|string',
        ]);

        $textToRewrite = '';

        if ($request->source_type === 'url') {
            try {
                $response = Http::timeout(15)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                ])->get($request->url);
                if ($response->successful()) {
                    // Extract basic text body to save tokens
                    $html = $response->body();
                    $textToRewrite = strip_tags(preg_replace('/<(script|style)[^>]*?>.*?<\/\1>/si', '', $html));
                    // Condense whitespace
                    $textToRewrite = preg_replace('/\s+/', ' ', $textToRewrite);
                    $textToRewrite = substr($textToRewrite, 0, 15000); // Limit length to avoid massive token usage
                } else {
                    return back()->withErrors(['error' => 'Gagal mengambil data dari URL. Situs mungkin memblokir akses otomatis. Gunakan teks manual.']);
                }
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Gagal mengambil data dari URL: ' . $e->getMessage()]);
            }
        } else {
            $textToRewrite = $request->manual_text;
        }

        if (empty(trim($textToRewrite))) {
            return back()->withErrors(['error' => 'Teks sumber kosong atau tidak bisa diekstrak dari URL.']);
        }

        // Send to OpenRouter (Claude 3.5 Sonnet)
        try {
            $apiKey = env('OPENROUTER_API_KEY');
            $model = env('OPENROUTER_DEFAULT_MODEL', 'openai/gpt-4o');

            $prompt = 'Kamu adalah jurnalis finansial profesional untuk media Avenir.
Tulis ulang berita/teks berikut menjadi artikel berita finansial yang tajam, orisinal, dan bergaya bahasa profesional Bahasa Indonesia.
Sertakan tag HTML yang sesuai untuk format konten (misal: p, h3, ul, li).
Output HANYA DALAM FORMAT JSON VALID tanpa tambahan apapun, dengan struktur:
{
    "title": "Judul Berita yang Catchy dan Profesional",
    "category": "Pilih SALAH SATU secara persis: Market, Corporate Action, Macroekonomi, Global, Commodity, Fixed Income, Emiten",
    "badge": "Tag Utama (misal: IHSG, Korporasi, Global)",
    "excerpt": "Ringkasan pendek 1-2 kalimat menarik",
    "content_html": "<p>Teks paragraf 1...</p><p>Teks paragraf 2...</p>"
}

Teks Sumber: ' . substr($textToRewrite, 0, 10000);

            $aiResponse = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'HTTP-Referer' => env('APP_URL', 'http://127.0.0.1'),
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            if ($aiResponse->successful()) {
                $resultJson = $aiResponse->json('choices.0.message.content');
                
                // Sometimes AI returns markdown wrapped like ```json\n{...}\n```
                $resultJson = preg_replace('/```json\s*/', '', $resultJson);
                $resultJson = preg_replace('/```\s*/', '', $resultJson);
                
                $result = json_decode(trim($resultJson), true);

                if (!$result) {
                    return back()->withErrors(['error' => 'Output AI bukan JSON yang valid. Response mentah: ' . substr($resultJson, 0, 200)]);
                }

                \App\Models\AILog::create([
                    'feature' => 'NewsGenerator',
                    'input_hash' => hash('sha256', $prompt),
                    'output' => $resultJson,
                    'model' => $model,
                    'sources' => ['source_type' => $request->source_type, 'url' => $request->url],
                    'reviewer_id' => auth()->id(),
                ]);

                return back()->with('generated_news', $result);
            }

            return back()->withErrors(['error' => 'Gagal menghubungi OpenRouter AI. Status: ' . $aiResponse->status() . ' - ' . $aiResponse->body()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    public function publish(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'badge' => 'required|string',
            'excerpt' => 'required|string',
            'cover_image_file' => 'nullable|image|max:5120', // Max 5MB
            'content_html' => 'required|string',
            'ticker_ids' => 'nullable|array',
            'ticker_ids.*' => 'exists:tickers,id'
        ]);

        $slug = Str::slug($request->title) . '-' . uniqid();

        // Assign a random finance-related unsplash image for cover if none provided
        $randomImage = 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?q=80&w=2070&auto=format&fit=crop';
        $coverImageUrl = $randomImage;

        if ($request->hasFile('cover_image_file')) {
            $path = $request->file('cover_image_file')->store('news-covers', 'public');
            $coverImageUrl = '/storage/' . $path;
        } else {
            $financeImages = [
                'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?q=80&w=2070&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?q=80&w=2070&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?q=80&w=2070&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1642543492481-44e81e3914a7?q=80&w=2070&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=2015&auto=format&fit=crop',
            ];
            $coverImageUrl = $financeImages[array_rand($financeImages)];
        }

        $article = Article::create([
            'title' => $request->title,
            'slug' => $slug,
            'category' => $request->category,
            'badge' => $request->badge,
            'excerpt' => $request->excerpt,
            'content' => '<div class="art-body">' . $request->content_html . '</div>',
            'cover_image' => $coverImageUrl,
            'author' => 'Avenir AI',
            'published_at' => now(),
            'is_paid' => false,
            'status' => 'published',
        ]);

        if ($request->has('ticker_ids') && is_array($request->ticker_ids)) {
            $article->tickers()->sync($request->ticker_ids);
        }

        return redirect()->route('admin.news-generator.index')->with('success', 'Berita berhasil diterbitkan ke halaman News!');
    }
}
