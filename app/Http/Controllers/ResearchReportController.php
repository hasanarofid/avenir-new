<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ResearchReportController extends Controller
{
    /**
     * Display a listing of the reports/articles.
     */
    public function index()
    {
        $articles = Article::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->get();

        return Inertia::render('Report/Index', [
            'articles' => $articles
        ]);
    }

    /**
     * Display the specified report.
     */
    public function show($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        // Load related ticker using the belongsToMany relationship
        $ticker = $article->tickers()->first();

        $isLocked = false;

        // Paywall Logic
        if ($article->is_paid) {
            $user = auth()->user();
            if (!$user || !$user->hasActivePremium()) {
                $isLocked = true;
                
                // Truncate the content for non-premium users
                // E.g., keeping only the first 500 characters or roughly one paragraph.
                $article->content = Str::limit(strip_tags($article->content, '<p><br><b><strong><i><em>'), 500) . '</p>';
            }
        }

        return Inertia::render('Report/Show', [
            'article' => $article,
            'ticker' => $ticker,
            'isLocked' => $isLocked,
        ]);
    }
}
