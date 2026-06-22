<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    /**
     * Display a public page by its slug.
     */
    public function show($slug)
    {
        // Temukan halaman yang aktif berdasarkan slug
        $page = Page::with(['sections' => function($query) {
                $query->where('is_active', true)->orderBy('order');
            }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Page', [
            'page' => [
                'title' => $page->title,
                'slug' => $page->slug,
                'sections' => $page->sections->map(function ($section) {
                    return [
                        'key' => $section->key,
                        'title' => $section->title,
                        'content' => $section->content,
                    ];
                })
            ]
        ])->withViewData([
            'meta' => [
                'title' => $page->title . ' | Avenir Research',
                'description' => $page->meta_description ?? 'Halaman ' . $page->title . ' dari Avenir Research.',
            ]
        ]);
    }
}
