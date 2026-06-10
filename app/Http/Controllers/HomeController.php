<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Research;
use App\Models\Article;

class HomeController extends Controller
{
    /**
     * Render landing page.
     */
    public function index()
    {
        return Inertia::render('Home');
    }

    /**
     * Render research catalog.
     */
    public function katalog()
    {
        $researches = Research::latest()->get();
        $unlockedTickers = auth()->check()
            ? \Illuminate\Support\Facades\DB::table('unlocked_research')
                ->where('user_id', auth()->id())
                ->pluck('ticker')
                ->toArray()
            : [];
        
        return Inertia::render('Dashboard', [
            'researches' => $researches,
            'unlockedTickers' => $unlockedTickers
        ]);
    }

    /**
     * Render research catalog detail by slug (or legacy ID).
     */
    public function katalogDetail($slug)
    {
        // Cari research berdasarkan slug
        $research = Research::where('slug', $slug)->first();

        // Fallback: jika slug tidak ketemu, coba cari by ID (untuk URL lama)
        if (!$research && is_numeric($slug)) {
            $research = Research::find((int) $slug);
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

        // Cek DB dulu sesuai permintaan user
        $content = $research->content;

        if (empty($content)) {
            // Baca konten dari file HTML di app/website/{slug}.html
            $filePath = storage_path("app/website/{$slug}.html");

            if (file_exists($filePath)) {
                $html = file_get_contents($filePath);

                // 1. Ambil konten di dalam guest-lock-content (untuk riset berbayar guest)
                $startToken = '<div class="guest-lock-content">';
                $endToken   = '<div class="guest-lock-overlay"';
                $startPos   = strpos($html, $startToken);
                if ($startPos !== false) {
                    $startPos += strlen($startToken);
                    $endPos = strpos($html, $endToken, $startPos);
                    if ($endPos !== false) {
                        $content = trim(substr($html, $startPos, $endPos - $startPos));
                    }
                }

                // 2. Fallback: ambil konten di dalam .cnt (misal CRM.html)
                if (!$content) {
                    $startToken = '<div class="cnt">';
                    $endToken   = '<footer';
                    $startPos   = strpos($html, $startToken);
                    if ($startPos !== false) {
                        $endPos = strpos($html, $endToken, $startPos);
                        if ($endPos !== false) {
                            $content = trim(substr($html, $startPos, $endPos - $startPos));
                        } else {
                            $content = trim(substr($html, $startPos));
                        }
                    }
                }

                // 3. Fallback: ambil konten di dalam .art-body
                if (!$content) {
                    $startToken = '<div class="art-body">';
                    $startPos   = strpos($html, $startToken);
                    if ($startPos !== false) {
                        $content = trim(substr($html, $startPos));
                        // Hapus footer jika ikut terbawa
                        if (($fPos = strpos($content, '<footer')) !== false) {
                            $content = substr($content, 0, $fPos);
                        }
                    }
                }

                // 4. Fallback: ambil dari <body> tapi hilangkan nav, drawer, dan hero
                if (!$content) {
                    $startPos = strpos($html, '<body>');
                    $endPos   = strpos($html, '</body>');
                    if ($startPos !== false && $endPos !== false) {
                        $rawBody = substr($html, $startPos + strlen('<body>'), $endPos - $startPos - strlen('<body>'));
                        // Buang elemen header legacy yang merusak tampilan
                        $rawBody = preg_replace('/<div class="nav">.*?<\/div>\s*<\/div>/s', '', $rawBody);
                        $rawBody = preg_replace('/<div class="drawer.*?<\/div>/s', '', $rawBody);
                        $rawBody = preg_replace('/<div class="overlay.*?<\/div>/s', '', $rawBody);
                        $rawBody = preg_replace('/<div class="hero">.*?<\/div>\s*<\/div>\s*<\/div>/s', '', $rawBody);
                        $rawBody = preg_replace('/<footer.*?<\/footer>/s', '', $rawBody);
                        $content = trim($rawBody);
                    }
                }
            }
        }

        // Default content jika file tidak ditemukan
        if (!$content) {
            $content = '<div class="art-body"><p>Detail laporan riset <strong>' 
                . htmlspecialchars($research->title) . ' (' . htmlspecialchars($research->ticker ?? '') . ')'
                . '</strong> sedang dalam proses migrasi ke platform baru.</p></div>';
        }

        $content = $this->cleanHtmlForDarkMode($content);

        return Inertia::render('KatalogDetail', [
            'research' => [
                'title'       => $research->title,
                'ticker'      => $research->ticker,
                'sector'      => $research->sector,
                'slug'        => $research->slug,
                'subtitle'    => $research->subtitle,
                'revenue'     => $research->revenue,
                'patmi'       => $research->patmi,
                'sales'       => $research->sales,
                'price'       => $research->price,
                'date'        => $research->date,
                'image'       => $research->image,
                'content'     => $content,
                'is_paid'     => true,
                'is_unlocked' => $isUnlocked,
            ],
        ]);
    }

    /**
     * Render article list.
     */
    public function artikel()
    {
        $articles = Article::where('status', 'published')
            ->orderByDesc('published_at')
            ->get();
        
        return Inertia::render('Artikel', [
            'articles' => $articles
        ]);
    }

    /**
     * Render article details with paywall logic.
     */
    public function artikelDetail($slug)
    {
        $article = Article::where('slug', $slug)
            ->where('status', 'published')
            ->first();

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
                'author'       => $article->author,
                'published_at' => $article->published_at 
                    ? $article->published_at->format('d M Y') 
                    : null,
                'is_paid'      => $isPaid,
                'is_unlocked'  => $isUnlocked,
                'content'      => $content,
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
        $newsList = Article::where('category', 'like', '%News%')
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->get()
            ->map(function ($news) {
                return [
                    'title' => $news->title,
                    'slug' => $news->slug,
                    'category' => $news->category,
                    'badge' => $news->badge,
                    'excerpt' => $news->excerpt,
                    'published_at' => $news->published_at ? $news->published_at->format('d M Y') : null,
                    'cover_image' => $news->cover_image,
                    'is_paid' => $news->is_paid,
                ];
            });

        return Inertia::render('News', [
            'newsList' => $newsList
        ]);
    }

    /**
     * Render news detail page.
     */
    public function newsDetail($slug)
    {
        $article = Article::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        // Legacy check / fallback
        if (!$article) {
            $filePath = storage_path("app/website/{$slug}.html");
            if (file_exists($filePath)) {
                $html = file_get_contents($filePath);
                $content = null;
                // Look for guest-lock-content or general content
                $startToken = '<div class="guest-lock-content">';
                $endToken = '<div class="guest-lock-overlay"';
                $startPos = strpos($html, $startToken);
                if ($startPos !== false) {
                    $startPos += strlen($startToken);
                    $endPos = strpos($html, $endToken, $startPos);
                    if ($endPos !== false) {
                        $content = trim(substr($html, $startPos, $endPos - $startPos));
                    }
                }
                
                if (!$content) {
                    $startToken = '<div class="news-page">';
                    $startPos = strpos($html, $startToken);
                    if ($startPos !== false) {
                        $content = trim(substr($html, $startPos));
                    }
                }

                if (!$content) {
                    $startToken = '<body>';
                    $endToken = '</body>';
                    $startPos = strpos($html, $startToken);
                    if ($startPos !== false) {
                        $startPos += strlen($startToken);
                        $endPos = strpos($html, $endToken, $startPos);
                        if ($endPos !== false) {
                            $content = trim(substr($html, $startPos, $endPos - $startPos));
                        }
                    }
                }
                
                return Inertia::render('NewsDetail', [
                    'news' => [
                        'title' => ucwords(str_replace('-', ' ', $slug)),
                        'slug' => $slug,
                        'category' => 'News · Market',
                        'published_at' => now()->format('d M Y'),
                        'is_paid' => false,
                        'is_unlocked' => true,
                        'content' => $this->cleanHtmlForDarkMode($content ?? '<div class="art-body"><p>Detail berita ini sedang dimigrasikan ke platform baru.</p></div>'),
                    ]
                ]);
            }
            abort(404);
        }

        $isLoggedIn = auth()->check();
        $isSubscriber = false;
        $isUnlocked = false;

        if ($isLoggedIn) {
            $isSubscriber = auth()->user()->hasActivePremium() || auth()->user()->hasRole('admin');
            $isUnlocked = $isSubscriber;
        }

        $isPaid = (bool) $article->is_paid;
        $content = $article->content;

        if ($isPaid && !$isLoggedIn) {
            $content = $this->truncateForGuest($content);
            $isUnlocked = false;
        }

        $content = $this->cleanHtmlForDarkMode($content);

        return Inertia::render('NewsDetail', [
            'news' => [
                'title' => $article->title,
                'slug' => $article->slug,
                'category' => $article->category,
                'published_at' => $article->published_at ? $article->published_at->format('d M Y') : null,
                'is_paid' => $isPaid,
                'is_unlocked' => $isUnlocked,
                'content' => $content,
                'cover_image' => $article->cover_image,
            ]
        ]);
    }



    /**
     * Render tentang page.
     */
    public function tentang()
    {
        return Inertia::render('About');
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
