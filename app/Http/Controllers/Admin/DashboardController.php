<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard home.
     */
    public function index()
    {
        $totalUsers = User::count();
        $activeSubscribers = \Illuminate\Support\Facades\DB::table('user_profiles')->where('is_subscriber', true)->count();
        $activeTrials = \Illuminate\Support\Facades\DB::table('trial_email_history')->count(); // Or any logic for trials
        $totalMitra = \Illuminate\Support\Facades\DB::table('partners')->where('is_verified', true)->count();
        $totalViews = \Illuminate\Support\Facades\DB::table('research_views')->count();
        
        $likesCount = \Illuminate\Support\Facades\DB::table('research_likes')->count();
        $commentsCount = \Illuminate\Support\Facades\DB::table('comments')->where('is_deleted', false)->count();

        // 30 days views trend mock (for now, or we can query real data)
        $trendData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = \Illuminate\Support\Facades\DB::table('research_views')
                        ->whereDate('viewed_at', $date)
                        ->count();
            $trendData[] = $count;
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users_count' => $totalUsers,
                'active_subscribers' => $activeSubscribers,
                'active_trials' => $activeTrials,
                'total_mitra' => $totalMitra,
                'total_views' => $totalViews,
                'likes_count' => $likesCount,
                'comments_count' => $commentsCount,
                'trend_data' => $trendData,
            ],
            'recent_posts' => Post::with('category')->latest()->take(5)->get(),
        ]);
    }
}
