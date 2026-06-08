<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function create()
    {
        $this->authorize('create articles');
        return Inertia::render('Articles/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create articles');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'position_disclosure' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . time();
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending_approval';

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('articles', 'public');
        }

        Article::create($validated);

        return redirect()->route('dashboard')->with('success', 'Artikel berhasil dikirim dan menunggu approval!');
    }
}
