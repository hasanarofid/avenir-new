<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::query()->with('author');

        if (!auth()->user()->hasRole('admin')) {
            $query->where('author_id', auth()->id());
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        $news = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Admin/News/Index', [
            'news' => $news,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/News/CreateEdit');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'sentiment' => 'nullable|string',
            'published_at' => 'nullable|date',
            'status' => 'required|string',
            'is_paid' => 'boolean',
            'image' => 'nullable|image|max:5120'
        ]);

        $data['slug'] = Str::slug($data['title']);
        // Ensure slug uniqueness
        $count = News::where('slug', 'LIKE', $data['slug'] . '%')->count();
        if ($count > 0) {
            $data['slug'] = $data['slug'] . '-' . $count;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news-covers', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }
        unset($data['image']);

        $data['author_id'] = auth()->id();

        News::create($data);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil ditambahkan');
    }

    public function edit(News $news)
    {
        return Inertia::render('Admin/News/CreateEdit', [
            'news' => $news
        ]);
    }

    public function update(Request $request, News $news)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'source_url' => 'nullable|url|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'sentiment' => 'nullable|string',
            'published_at' => 'nullable|date',
            'status' => 'required|string',
            'is_paid' => 'boolean',
            'image' => 'nullable|image|max:5120'
        ]);

        if ($data['title'] !== $news->title) {
            $data['slug'] = Str::slug($data['title']);
            $count = News::where('slug', 'LIKE', $data['slug'] . '%')->where('id', '!=', $news->id)->count();
            if ($count > 0) {
                $data['slug'] = $data['slug'] . '-' . $count;
            }
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($news->cover_image) {
                $oldPath = str_replace('/storage/', '', $news->cover_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            $path = $request->file('image')->store('news-covers', 'public');
            $data['cover_image'] = '/storage/' . $path;
        }
        unset($data['image']);

        $data['author_id'] = auth()->id();

        $news->update($data);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui');
    }

    public function destroy(News $news)
    {
        if ($news->cover_image) {
            $oldPath = str_replace('/storage/', '', $news->cover_image);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil dihapus');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:news,id'
        ]);

        $newsItems = News::whereIn('id', $request->ids)->get();

        foreach ($newsItems as $news) {
            if ($news->cover_image) {
                $oldPath = str_replace('/storage/', '', $news->cover_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $news->delete();
        }

        return redirect()->back()->with('success', 'Berita terpilih berhasil dihapus');
    }
}
