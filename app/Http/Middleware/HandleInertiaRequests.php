<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'notifications' => DB::table('notifications')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($n) => [
                    'id'           => $n->id,
                    'title'        => $n->title,
                    'category'     => $n->category ?? 'General',
                    'url'          => $n->url ?? null,
                    'is_new'       => $n->is_new ?? true,
                    'published_at' => $n->published_at ?? $n->created_at,
                ]),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'generated_news' => $request->session()->get('generated_news'),
            ],
        ];
    }
}
