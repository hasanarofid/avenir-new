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
        $researches = Research::all();
        
        return Inertia::render('Dashboard', [
            'researches' => $researches
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
            ->firstOrFail();

        $content = $this->getArticleContent($article->slug) ?? $article->content;

        $isPaid = (bool) $article->is_paid;
        $isGuest = !auth()->check();

        // Secure paywall check: truncate content sent to guests for paid articles
        if ($isPaid && $isGuest) {
            $content = $this->truncateForGuest($content);
        }

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
                'content'      => $content,
            ]
        ]);
    }

    /**
     * Read static HTML file for article content if exists.
     */
    private function getArticleContent($slug)
    {
        $filePath = base_path("app/website/artikel-{$slug}.html");
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
}
