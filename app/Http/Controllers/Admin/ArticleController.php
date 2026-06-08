<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function index()
    {
        $this->authorize('publish articles');
        return Inertia::render('Admin/Articles/Index', [
            'articles' => Article::with('author')->where('status', 'pending_approval')->latest()->get(),
        ]);
    }

    public function publish(Article $article)
    {
        $this->authorize('publish articles');
        $article->publish();

        return redirect()->back()->with('success', 'Artikel berhasil dipublikasikan!');
    }

    public function reject(Article $article)
    {
        $this->authorize('publish articles');
        $article->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Artikel berhasil ditolak!');
    }
}
