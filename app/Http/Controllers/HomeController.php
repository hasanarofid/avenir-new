<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Research;
use App\Models\Article;
use App\Models\News;

class HomeController extends Controller
{
    /**
     * Render landing page.
     */
    public function index()
    {
        // 1. Fetch Research as Research Desk
        $dbResearches = \App\Models\Research::with('emiten')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
            
        $risetUnggulan = [];
        foreach ($dbResearches as $r) {
            $rating = strtoupper($r->recommendation);
            if (empty($rating)) {
                $rating = 'BUY';
            }
            
            $upsideVal = floatval($r->upside);

            $risetUnggulan[] = [
                'ticker' => $r->ticker ?? 'N/A',
                'name' => $r->emiten ? $r->emiten->company_name : $r->title,
                'sector' => $r->sector ?? 'Financials',
                'targetPrice' => 'Rp ' . number_format((float)$r->target_price, 0, ',', '.'),
                'upside' => ($upsideVal >= 0 ? '+' : '') . number_format($upsideVal, 1) . '%',
                'rating' => $rating,
                'date' => $r->date ? \Carbon\Carbon::parse($r->date)->format('d M Y') : ($r->created_at ? $r->created_at->format('d M Y') : now()->format('d M Y')),
                'slug' => $r->slug
            ];
        }

        // 2. Fetch Latest Articles as Insight Terbaru
        $dbArticles = \App\Models\Article::select(['id', 'title', 'cover_image', 'published_at', 'created_at'])
            ->where('category', '!=', 'news')
            ->where('status', 'published')
            ->latest()
            ->take(3)
            ->get();
        $insightTerbaru = [];
        $gradients = [
            'from-blue-950/60 to-emerald-950/60',
            'from-cyan-950/60 to-blue-950/60',
            'from-teal-950/60 to-emerald-950/60'
        ];
        foreach ($dbArticles as $index => $art) {
            $insightTerbaru[] = [
                'title' => $art->title,
                'date' => $art->published_at ? $art->published_at->format('d M Y') : $art->created_at->format('d M Y'),
                'gradient' => $gradients[$index % count($gradients)],
                'image' => $art->cover_image,
            ];
        }

        // 3. Fetch Latest Headlines
        $dbNews = \App\Models\News::select(['id', 'title', 'published_at', 'created_at'])
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();
        
        // If news is empty, fallback to recent posts of any category
        if ($dbNews->isEmpty()) {
            $dbNews = \App\Models\Article::select(['id', 'title', 'published_at', 'created_at'])
                ->where('status', 'published')
                ->latest()
                ->take(5)
                ->get();
        }

        $headlinesPasar = [];
        foreach ($dbNews as $news) {
            $tickerTag = 'AVENIR';
            if (preg_match('/^[A-Z]{4}/', $news->title, $match)) {
                $tickerTag = $match[0];
            } else {
                $tickerRelation = \Illuminate\Support\Facades\DB::table('article_ticker')
                    ->join('tickers', 'article_ticker.ticker_id', '=', 'tickers.id')
                    ->where('article_ticker.article_id', $news->id)
                    ->first();
                if ($tickerRelation) {
                    $tickerTag = $tickerRelation->symbol;
                }
            }

            $headlinesPasar[] = [
                'ticker' => $tickerTag,
                'text' => $news->title,
                'time' => $news->published_at ? $news->published_at->format('H:i') : $news->created_at->format('H:i')
            ];
        }

        return Inertia::render('Home', [
            'risetUnggulan' => $risetUnggulan,
            'insightTerbaru' => $insightTerbaru,
            'headlinesPasar' => $headlinesPasar
        ])->withViewData([
            'meta' => [
                'title' => 'Avenir Research | Platform Riset Ekuitas Indonesia',
                'description' => 'Akses laporan riset mendalam untuk keputusan investasi yang lebih cerdas. Disusun oleh tim analis berpengalaman.',
            ]
        ]);
    }

    /**
     * Render research catalog.
     */
    public function katalog()
    {
        $researches = Research::with(['author', 'emiten'])->select([
            'id', 'title', 'ticker', 'sector', 'slug', 'subtitle', 'revenue', 
            'patmi', 'sales', 'tags', 'date', 'price', 'recommendation', 
            'target_price', 'upside', 'report_type', 'is_premium', 'pdf_path', 
            'image', 'author_id', 'created_at', 'updated_at'
        ])->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->get()->map(function ($r) {
            $r->company_name = $r->emiten ? $r->emiten->company_name : null;
            return $r;
        });
        $unlockedTickers = auth()->check()
            ? \Illuminate\Support\Facades\DB::table('unlocked_research')
                ->where('user_id', auth()->id())
                ->pluck('ticker')
                ->toArray()
            : [];
        
        return Inertia::render('Dashboard', [
            'researches' => $researches,
            'unlockedTickers' => $unlockedTickers
        ])->withViewData([
            'meta' => [
                'title' => 'Katalog Riset | Avenir Research',
                'description' => 'Jelajahi berbagai laporan riset saham, rekomendasi, dan target price dari Avenir Research.',
            ]
        ]);
    }

    /**
     * Render research catalog detail by slug (or legacy ID).
     */
    public function katalogDetail($slug)
    {
        // Redirect legacy slugs to modern normalized slugs for consistency and SEO
        $slugMap = [
            'salesforce-inc' => 'crm',
            'avia-avian' => 'avia',
            'petrosea' => 'ptro',
            'cisarua-mountain-dairy' => 'cmry',
            'crbs' => 'cbrs',
        ];
        if (isset($slugMap[$slug])) {
            return redirect()->route('katalog.detail', ['slug' => $slugMap[$slug]], 301);
        }

        // Cari research berdasarkan slug
        $research = Research::with(['author', 'emiten'])->where('slug', $slug)->first();

        // Fallback: jika slug tidak ketemu, coba cari by ID (untuk URL lama)
        if (!$research && is_numeric($slug)) {
            $research = Research::with(['author', 'emiten'])->find((int) $slug);
            // Redirect ke URL slug jika ditemukan
            if ($research && $research->slug) {
                return redirect()->route('katalog.detail', ['slug' => $research->slug], 301);
            }
        }

        if (!$research) {
            abort(404);
        }

        // Cek apakah user punya akses
        $isSubscriber = false;
        $isUnlocked   = false;
        if (auth()->check()) {
            $profile = \Illuminate\Support\Facades\DB::table('user_profiles')
                ->where('user_id', auth()->id())
                ->first();
            if ($profile && $profile->is_subscriber) {
                $isSubscriber = true;
                $isUnlocked   = true;
            }
            if (!$isUnlocked && $research->ticker) {
                $isUnlocked = \Illuminate\Support\Facades\DB::table('unlocked_research')
                    ->where('user_id', auth()->id())
                    ->where('ticker', $research->ticker)
                    ->exists();
            }

            // Trial limit: non-subscriber only gets access to N newest research
            if (!$isSubscriber && !$isUnlocked) {
                $trialLimit  = (int) \App\Models\Setting::getValue('trial_riset_limit', 3);
                $allowedIds  = Research::orderByDesc('date')->take($trialLimit)->pluck('id');
                $isUnlocked  = $allowedIds->contains($research->id);
            }
        }

        // ── View Log: catat kunjungan, throttle 24 jam per user/IP ──────────
        $ip = request()->ip();
        $userId = auth()->id();

        $alreadyViewed = \App\Models\ResearchViewLog::where('research_id', $research->id)
            ->where(function ($q) use ($userId, $ip) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('ip_address', $ip)->whereNull('user_id');
                }
            })
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if (!$alreadyViewed) {
            \App\Models\ResearchViewLog::create([
                'research_id' => $research->id,
                'user_id'     => $userId,
                'ip_address'  => $userId ? null : $ip, // tidak simpan IP jika sudah ada user_id
                'created_at'  => now(),
            ]);
        }

        // ── Stats: hitung views, comments, bookmark ──────────────────────────
        $viewsCount    = \App\Models\ResearchViewLog::where('research_id', $research->id)->count();
        $commentsCount = \App\Models\Comment::where('research_id', $research->id)->count();
        $isBookmarked  = $userId
            ? \App\Models\ResearchBookmark::where('research_id', $research->id)->where('user_id', $userId)->exists()
            : false;

        $comments = $research->comments()->with('user:id,name')->latest()->get()->map(function($comment) {
            return [
                'id'          => $comment->id,
                'author_name' => $comment->user ? $comment->user->name : ($comment->guest_name ?: 'Guest'),
                'content'     => $comment->content,
                'date'        => $comment->created_at->diffForHumans(),
            ];
        });

        return Inertia::render('KatalogDetail', [
            'research' => [
                'id'             => $research->id,
                'title'          => $research->title,
                'ticker'         => $research->ticker,
                'company_name'   => $research->emiten ? $research->emiten->company_name : null,
                'sector'         => $research->sector,
                'slug'           => $research->slug,
                'subtitle'       => $research->subtitle,
                'revenue'        => $research->revenue,
                'patmi'          => $research->patmi,
                'sales'          => $research->sales,
                'price'          => $research->price,
                'date'           => $research->date,
                'image'          => $research->image,
                'recommendation' => $research->recommendation,
                'target_price'   => $research->target_price,
                'upside'         => $research->upside,
                'report_type'    => $research->report_type,
                'is_premium'     => $research->is_premium,
                'pdf_path'       => $isUnlocked ? $research->pdf_path : null,
                'content'        => $research->content,
                'author'         => $research->author,
                'created_at'     => $research->created_at,
                'is_paid'        => $research->is_premium,
                'is_unlocked'    => $isUnlocked,
                'comments'       => $comments,
                // Stats realtime
                'views_count'    => $viewsCount,
                'comments_count' => $commentsCount,
                'is_bookmarked'  => $isBookmarked,
            ],
        ])->withViewData([
            'meta' => [
                'title' => ($research->ticker ? $research->ticker . ' — ' : '') . $research->title . ' | Avenir Research',
                'description' => \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($research->subtitle ?? $research->content))), 150),
                'image' => $research->image ? asset($research->image) : asset('favicon.png'),
                'type' => 'article',
            ]
        ]);
    }

    /**
     * Render article list.
     */
    public function artikel()
    {
        $selectColumns = ['id', 'title', 'slug', 'category', 'badge', 'excerpt', 'cover_image', 'user_id', 'published_at', 'created_at', 'status'];

        $articles = Article::with('author')->select($selectColumns)->where('status', 'published')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $editorPicks = Article::with('author')->select($selectColumns)->where('status', 'published')
            ->whereNotNull('badge')
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get();
            
        // Fallback for Editor Picks if there are fewer than 3
        if ($editorPicks->count() < 3) {
            $excludeIds = $editorPicks->pluck('id')->toArray();
            $fallback = Article::with('author')->select($selectColumns)->where('status', 'published')
                ->when(count($excludeIds) > 0, function($q) use ($excludeIds) {
                    return $q->whereNotIn('id', $excludeIds);
                })
                ->inRandomOrder()
                ->take(3 - $editorPicks->count())
                ->get();
            $editorPicks = $editorPicks->concat($fallback);
        }

        // Trending articles
        $trendingArticles = Article::with('author')->select($selectColumns)->where('status', 'published')
            ->inRandomOrder()
            ->take(5)
            ->get();

        // Topik Populer
        $populerTopics = \Illuminate\Support\Facades\DB::table('articles')
            ->select('category as name', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->where('status', 'published')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('count')
            ->take(10)
            ->get();
        
        return Inertia::render('Artikel', [
            'articles' => $articles,
            'editorPicks' => $editorPicks,
            'trendingArticles' => $trendingArticles,
            'populerTopics' => $populerTopics
        ])->withViewData([
            'meta' => [
                'title' => 'Artikel & Insight | Avenir Research',
                'description' => 'Baca artikel, insight, dan analisis terbaru mengenai pasar modal Indonesia.',
            ]
        ]);
    }

    /**
     * Render article details with paywall logic.
     */
    public function artikelDetail($slug)
    {
        $article = Article::with('author')->where('slug', $slug)
            ->where('status', 'published')
            ->first();
            // dd($article->author());

        // Fallback: jika slug tidak ketemu, coba cari by ID (untuk URL lama)
        if (!$article && is_numeric($slug)) {
            $article = Article::find((int) $slug);
            // Redirect ke URL slug jika ditemukan
            if ($article && $article->slug && $article->status === 'published') {
                return redirect()->route('artikel.detail', ['slug' => $article->slug], 301);
            }
        }

        if (!$article) {
            abort(404);
        }

        $isLoggedIn = auth()->check();
        $isSubscriber = false;
        $isUnlocked = false;

        if ($isLoggedIn) {
            $isSubscriber = auth()->user()->hasActivePremium() || auth()->user()->hasRole('admin');
            $isUnlocked = $isSubscriber;
        }

        $content = $this->getArticleContent($article->slug) ?? $article->content;

        $isPaid   = (bool) $article->is_paid;
        $isTrial  = false;

        // Trial check: logged-in non-subscribers are treated as trial users
        if ($isLoggedIn && !$isSubscriber) {
            $profile = \Illuminate\Support\Facades\DB::table('user_profiles')
                ->where('user_id', auth()->id())
                ->first();
            $isTrial = !($profile && $profile->is_subscriber);
        }

        // Trial paywall: limit access to N newest articles
        if ($isTrial) {
            $trialLimit = (int) \App\Models\Setting::getValue('trial_artikel_limit', 3);
            $allowedIds = \App\Models\Article::where('status', 'published')
                ->orderByDesc('published_at')
                ->take($trialLimit)
                ->pluck('id');
            if (!$allowedIds->contains($article->id)) {
                // Outside trial limit — truncate same as guest paywall
                $content = $this->truncateForGuest($content);
                $isUnlocked = false;
            } else {
                $isUnlocked = true;
            }
        }

        // Secure paywall check: truncate content sent to guests for paid articles
        if ($isPaid && !$isLoggedIn) {
            $content = $this->truncateForGuest($content);
            $isUnlocked = false;
        }

        $content = $this->cleanHtmlForDarkMode($content);

        return Inertia::render('ArtikelDetail', [
            'article' => [
                'id'           => $article->id,
                'title'        => $article->title,
                'slug'         => $article->slug,
                'category'     => $article->category,
                'badge'        => $article->badge,
                'excerpt'      => $article->excerpt,
                'cover_image'  => $article->cover_image,
                'author' => $article->getRelation('author'),
                'published_at' => $article->published_at 
                    ? $article->published_at->format('d M Y') 
                    : null,
                'is_paid'      => $isPaid,
                'is_unlocked'  => $isUnlocked,
                'content'      => $content,
            ]
        ])->withViewData([
            'meta' => [
                'title' => $article->title . ' | Avenir Research',
                'description' => $article->excerpt ? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($article->excerpt))), 150) : \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($content))), 150),
                'image' => $article->cover_image ? asset($article->cover_image) : asset('favicon.png'),
                'type' => 'article',
            ]
        ]);
    }

    /**
     * Read static HTML file for article content if exists.
     */
    private function getArticleContent($slug)
    {
        $filePath = storage_path("app/website/artikel-{$slug}.html");
        if (!file_exists($filePath)) {
            return null;
        }

        $html = file_get_contents($filePath);

        $startToken = '<div class="guest-lock-content">';
        $endToken = '<div class="guest-lock-overlay"';

        $startPos = strpos($html, $startToken);
        if ($startPos !== false) {
            $startPos += strlen($startToken);
            $endPos = strpos($html, $endToken, $startPos);
            if ($endPos !== false) {
                return trim(substr($html, $startPos, $endPos - $startPos));
            }
        }

        // Fallback: search for <div class="art-page">
        $startToken = '<div class="art-page">';
        $startPos = strpos($html, $startToken);
        if ($startPos !== false) {
            return trim(substr($html, $startPos));
        }

        return null;
    }

    /**
     * Truncate article body for non-authenticated guest users.
     */
    private function truncateForGuest($content)
    {
        $bodyToken = '<div class="art-body">';
        $pos = strpos($content, $bodyToken);
        if ($pos !== false) {
            $afterBody = substr($content, $pos + strlen($bodyToken));
            
            // Find the second paragraph closing tag </p>
            $pPos = -1;
            for ($i = 0; $i < 2; $i++) {
                $nextP = strpos($afterBody, '</p>', $pPos + 1);
                if ($nextP !== false) {
                    $pPos = $nextP;
                } else {
                    break;
                }
            }
            if ($pPos !== false && $pPos > 0) {
                $truncatedBody = substr($afterBody, 0, $pPos + 4);
                $beforeBody = substr($content, 0, $pos + strlen($bodyToken));
                
                // Add a nice fade gradient and close the art-body div
                return $beforeBody . $truncatedBody . '</div>';
            }
        }

        return substr($content, 0, 2000);
    }

    /**
     * Render news list page.
     */
    public function news()
    {
        $newsList = News::with('author')
            ->select(['id', 'title', 'slug', 'category', 'sentiment', 'source', 'excerpt', 'published_at', 'created_at', 'cover_image', 'author_id', 'status'])
            ->where('status', 'published')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($news) {
                return [
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'category' => $news->category,
                    'sentiment' => $news->sentiment,
                    'source' => $news->source,
                    'excerpt' => $news->excerpt,
                    'published_at' => $news->published_at ? $news->published_at->format('d M Y') : null,
                    'cover_image' => $news->cover_image,
                    'author' => $news->author,
                ];
            });

        $marketData = \Illuminate\Support\Facades\Cache::get('market_summary', []);

        $settings = \App\Models\Setting::pluck('value', 'key');
        $topTickersStr = $settings['market_top_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, AMMN.JK, MDKA.JK';
        $watchlistStr = $settings['market_watchlist_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, MDKA.JK';
        $trendingStr = $settings['market_trending_tickers'] ?? 'BBRI.JK, TLKM.JK, ASII.JK, MDKA.JK, AMMN.JK, GOTO.JK';

        return Inertia::render('News', [
            'newsList' => $newsList,
            'marketData' => $marketData,
            'marketConfig' => [
                'top' => array_filter(array_map('trim', explode(',', $topTickersStr))),
                'watchlist' => array_filter(array_map('trim', explode(',', $watchlistStr))),
                'trending' => array_filter(array_map('trim', explode(',', $trendingStr))),
            ]
        ])->withViewData([
            'meta' => [
                'title' => 'Berita Pasar | Avenir Research',
                'description' => 'Ikuti perkembangan dan berita terbaru dari pasar modal Indonesia.',
            ]
        ]);
    }

    /**
     * Render news detail page.
     */
    public function newsDetail($slug)
    {
        $news = News::with('author')->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $content = $this->cleanHtmlForDarkMode($news->content);

        return Inertia::render('NewsDetail', [
            'news' => [
                'title' => $news->title,
                'slug' => $news->slug,
                'category' => $news->category,
                'source' => $news->source,
                'sentiment' => $news->sentiment,
                'published_at' => $news->published_at ? $news->published_at->format('d M Y') : null,
                'content' => $content,
                'cover_image' => $news->cover_image,
                'author' => $news->author,
            ]
        ])->withViewData([
            'meta' => [
                'title' => $news->title . ' | Avenir Research',
                'description' => $news->excerpt ? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($news->excerpt))), 150) : \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($content))), 150),
                'image' => $news->cover_image ? asset($news->cover_image) : asset('favicon.png'),
                'type' => 'article',
            ]
        ]);
    }

    /**
     * API Endpoint to fetch historical OHLC chart data.
     */
    public function marketChartApi(Request $request, $symbol)
    {
        $range = $request->query('range', '1D');
        $service = new \App\Services\MarketDataService();
        $data = $service->getHistoricalData($symbol, $range);
        return response()->json($data);
    }

    /**
     * Render tentang page.
     */
    public function tentang()
    {
        return Inertia::render('About')->withViewData([
            'meta' => [
                'title' => 'Tentang Kami | Avenir Research',
                'description' => 'Pelajari lebih lanjut tentang visi, misi, dan tim profesional di balik Avenir Research.',
            ]
        ]);
    }

    /**
     * Render mitra page.
     */
    public function mitra()
    {
        $partners = \Illuminate\Support\Facades\DB::table('partners')
            ->join('users', 'partners.user_id', '=', 'users.id')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->where('partners.is_verified', true)
            ->select(
                'users.id',
                'users.name',
                'partners.certification',
                'partners.specializations',
                'user_profiles.first_name',
                'user_profiles.last_name'
            )
            ->get()
            ->map(function($p) {
                return [
                    'name' => $p->name ?? (trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: 'Mitra Analis'),
                    'certification' => $p->certification ?? 'Mitra Analis',
                    'specializations' => json_decode($p->specializations ?? '[]'),
                ];
            });

        return Inertia::render('Partners', [
            'partners' => $partners
        ])->withViewData([
            'meta' => [
                'title' => 'Mitra Analis | Avenir Research',
                'description' => 'Temui para mitra analis independen kami yang menyediakan riset pasar modal berkualitas.',
            ]
        ]);
    }

    /**
     * Render subscription page.
     */
    public function langganan()
    {
        $status = 'free'; // default
        $pendingSubmission = null;
        $isSubscriber = false;

        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user is subscriber in user_profiles
            $profile = \Illuminate\Support\Facades\DB::table('user_profiles')
                ->where('user_id', $user->id)
                ->first();
            if ($profile && $profile->is_subscriber) {
                $isSubscriber = true;
                $status = 'active';
            }

            // Check if there is a pending payment submission
            $pendingSubmission = \Illuminate\Support\Facades\DB::table('payment_submissions')
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->orderBy('submitted_at', 'desc')
                ->first();
            
            if ($pendingSubmission) {
                $status = 'pending';
            }
        } else {
            $status = 'guest';
        }

        return Inertia::render('Subscription', [
            'status' => $status,
            'isSubscriber' => $isSubscriber,
            'bankAccountInfo' => \App\Models\Setting::getValue('bank_account_info', 'Marta Fikri 3370748356 bank BCA'),
            'pendingSubmission' => $pendingSubmission ? [
                'paket' => $pendingSubmission->paket,
                'nominal' => $pendingSubmission->nominal,
                'submitted_at' => $pendingSubmission->submitted_at,
            ] : null
        ])->withViewData([
            'meta' => [
                'title' => 'Berlangganan | Avenir Research',
                'description' => 'Dapatkan akses penuh ke laporan riset premium, insight pasar, dan rekomendasi saham eksklusif.',
            ]
        ]);
    }

    /**
     * Handle payment submission request.
     */
    public function kirimPembayaran(Request $request)
    {
        $request->validate([
            'paket' => 'required|string|in:1bulan,3bulan,6bulan,12bulan',
            'durasi_hari' => 'required|integer',
            'nominal' => 'required|integer',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $user = auth()->user();

        // Check if there is already a pending submission to prevent duplicates
        $existingPending = \Illuminate\Support\Facades\DB::table('payment_submissions')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->withErrors(['error' => 'Anda masih memiliki submission yang sedang diverifikasi.']);
        }

        // Store file
        $file = $request->file('bukti_transfer');
        $path = $file->store('payment_proofs', 'public');
        $url = asset('storage/' . $path);

        // Get user profile first name and last name
        $profile = \Illuminate\Support\Facades\DB::table('user_profiles')
            ->where('user_id', $user->id)
            ->first();

        $userNama = $profile ? trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) : $user->name;

        // Insert using query builder
        \Illuminate\Support\Facades\DB::table('payment_submissions')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'user_id' => $user->id,
            'paket' => $request->input('paket'),
            'durasi_hari' => $request->input('durasi_hari'),
            'nominal' => $request->input('nominal'),
            'bukti_url' => $url,
            'bukti_path' => $path,
            'status' => 'pending',
            'submitted_at' => now(),
            'user_email' => $user->email,
            'user_nama' => $userNama ?: $user->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('langganan')->with('success', 'Bukti transfer berhasil dikirim. Admin akan segera memverifikasinya.');
    }

    /**
     * Cleans up dark inline text colors from statically exported HTML
     * if they are not inside a light-colored background box.
     */
    private function cleanHtmlForDarkMode($html) {
        if (empty($html)) return $html;

        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//*[@style]');

        foreach ($elements as $el) {
            $style = $el->getAttribute('style');
            
            $hasLightBg = false;
            $current = $el;
            while ($current !== null && $current->nodeType === XML_ELEMENT_NODE) {
                if ($current->hasAttribute('style')) {
                    $currStyle = $current->getAttribute('style');
                    if (preg_match('/background(-color)?\s*:\s*(#[eEfF][a-zA-Z0-9]{2,5}|rgba?\([^)]+,\s*0\.[0-9]+\s*\))/i', $currStyle)) {
                        $hasLightBg = true;
                        break;
                    }
                }
                $current = $current->parentNode;
            }
            
            if (!$hasLightBg) {
                $style = preg_replace('/color\s*:\s*#(1f2937|0f172a|374151|475569|000000|111827|1a1a1a|080D09|4b5563|6b7280|64748b|333333|222222)\s*;?/i', '', $style);
                if (trim($style) === '' || trim($style) === ';') {
                    $el->removeAttribute('style');
                } else {
                    $el->setAttribute('style', $style);
                }
            }
        }

        $cleaned = $dom->saveHTML();
        $cleaned = str_replace('<?xml encoding="UTF-8">', '', $cleaned);
        return trim($cleaned);
    }
}
