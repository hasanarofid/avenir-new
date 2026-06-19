<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()->with('author');

        if (!auth()->user()->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        $articles = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Articles/Index', [
            'articles' => $articles,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Articles/CreateEdit');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'is_paid' => 'boolean',
            'status' => 'required|string',
            'image' => 'nullable|image|max:5120'
        ]);

        $data['slug'] = Str::slug($data['title']);
        $count = Article::where('slug', 'LIKE', $data['slug'] . '%')->count();
        if ($count > 0) {
            $data['slug'] = $data['slug'] . '-' . $count;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('articles-covers', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }
        unset($data['image']);

        $data['user_id'] = auth()->id();
        $data['author'] = auth()->user()->name ?? 'Tim Avenir Research';
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        Article::create($data);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil ditambahkan');
    }

    public function edit(Article $article)
    {
        return Inertia::render('Admin/Articles/CreateEdit', [
            'article' => $article
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'is_paid' => 'boolean',
            'status' => 'required|string',
            'image' => 'nullable|image|max:5120'
        ]);

        if ($data['title'] !== $article->title) {
            $data['slug'] = Str::slug($data['title']);
            $count = Article::where('slug', 'LIKE', $data['slug'] . '%')->where('id', '!=', $article->id)->count();
            if ($count > 0) {
                $data['slug'] = $data['slug'] . '-' . $count;
            }
        }

        if ($request->hasFile('image')) {
            if ($article->cover_image) {
                $oldPath = str_replace('/storage/', '', $article->cover_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('image')->store('articles-covers', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }
        unset($data['image']);

        if ($data['status'] === 'published' && !$article->published_at) {
            $data['published_at'] = now();
        }

        $data['user_id'] = auth()->id();
        $data['author'] = auth()->user()->name ?? 'Tim Avenir Research';

        $article->update($data);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diperbarui');
    }

    public function destroy(Article $article)
    {
        if ($article->cover_image) {
            $oldPath = str_replace('/storage/', '', $article->cover_image);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dihapus');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id'
        ]);

        $articles = Article::whereIn('id', $request->ids)->get();

        foreach ($articles as $article) {
            if ($article->cover_image) {
                $oldPath = str_replace('/storage/', '', $article->cover_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $article->delete();
        }

        return redirect()->back()->with('success', 'Artikel terpilih berhasil dihapus');
    }
}
