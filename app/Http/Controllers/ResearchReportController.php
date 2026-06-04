<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ResearchReportController extends Controller
{
    /**
     * Display a listing of the reports/articles.
     */
    public function index()
    {
        // Fetch articles from the database. 
        // Using DB facade to allow connecting directly to Supabase if configured in .env
        $articles = DB::table('articles')
            ->where('status', 'published')
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
        $article = DB::table('articles')
            ->where('slug', $slug)
            ->first();

        if (!$article) {
            abort(404);
        }

        // We might also want to fetch related ticker data if applicable
        // For example, if the article is about BBRI, fetch BBRI ticker
        $ticker = null;
        if (isset($article->category) && $article->category === 'emiten') {
            // Assuming the title or a specific field holds the symbol (e.g. BBRI)
            // Just an example logic:
            $symbol = explode(' ', $article->title)[0]; 
            $ticker = DB::table('tickers')->where('symbol', $symbol)->first();
        }

        return Inertia::render('Report/Show', [
            'article' => $article,
            'ticker' => $ticker,
        ]);
    }
}
