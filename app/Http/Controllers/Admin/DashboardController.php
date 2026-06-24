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
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            return Inertia::render('Admin/GenericDashboard', [
                'user' => $user
            ]);
        }

        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count();

        $activeSubscribers = \Illuminate\Support\Facades\DB::table('user_profiles')->where('is_subscriber', true)->count();
        $activeTrials = \Illuminate\Support\Facades\DB::table('trial_email_history')->where('first_trial_at', '>=', now()->subDays(7))->count();
        $totalMitra = \Illuminate\Support\Facades\DB::table('partners')->where('is_verified', true)->count();
        
        // Exclude internal Tim Avenir if we had a specific logic, here just count all for now.
        $totalViews = \Illuminate\Support\Facades\DB::table('research_view_logs')->count();
        
        $likesCount = \Illuminate\Support\Facades\DB::table('app_likes')->where('likeable_type', 'App\Models\Research')->count();
        $commentsCount = \Illuminate\Support\Facades\DB::table('app_comments')->where('commentable_type', 'App\Models\Research')->count() + \Illuminate\Support\Facades\DB::table('comments')->count();

        // 30 days views trend real data
        $trendData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = \Illuminate\Support\Facades\DB::table('research_view_logs')
                        ->whereDate('created_at', $date)
                        ->count();
            $trendData[] = $count;
        }

        // Top 10 Research
        $topResearchQuery = \Illuminate\Support\Facades\DB::table('research_view_logs')
            ->select('research_id', \Illuminate\Support\Facades\DB::raw('count(*) as views_count'))
            ->groupBy('research_id')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        $topResearchIds = $topResearchQuery->pluck('research_id')->toArray();
        $topResearchModels = \Illuminate\Support\Facades\DB::table('research')
            ->leftJoin('users', 'research.author_id', '=', 'users.id')
            ->whereIn('research.id', $topResearchIds)
            ->select('research.id', 'research.title', 'research.ticker', 'users.name as author_name')
            ->get()
            ->keyBy('id');

        $topResearch = $topResearchQuery->map(function($view) use ($topResearchModels) {
            $meta = $topResearchModels->get($view->research_id);
            return [
                'id' => $view->research_id,
                'title' => $meta ? $meta->title : 'Unknown Research',
                'ticker' => $meta ? $meta->ticker : '-',
                'author_type' => $meta ? $meta->author_name : 'Tim Avenir',
                'views_count' => $view->views_count,
            ];
        });

        // Top Authors
        $topAuthorsQuery = \Illuminate\Support\Facades\DB::table('research_view_logs')
            ->join('research', 'research_view_logs.research_id', '=', 'research.id')
            ->leftJoin('users', 'research.author_id', '=', 'users.id')
            ->select(\Illuminate\Support\Facades\DB::raw('COALESCE(users.name, "Tim Avenir") as author_display_name'), \Illuminate\Support\Facades\DB::raw('count(research_view_logs.id) as views_count'))
            ->groupBy('author_display_name')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        // Heatmap Aktivitas
        $heatmapQuery = \Illuminate\Support\Facades\DB::table('research_view_logs')
            ->select(
                \Illuminate\Support\Facades\DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                \Illuminate\Support\Facades\DB::raw('HOUR(created_at) as hour_of_day'),
                \Illuminate\Support\Facades\DB::raw('count(*) as views_count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('day_of_week', 'hour_of_day')
            ->get();

        // Financials (MRR approx)
        $mrr = \Illuminate\Support\Facades\DB::table('payment_submissions')
            ->where('status', 'verified')
            ->where('updated_at', '>=', now()->subDays(30))
            ->sum('nominal');

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users_count' => $totalUsers,
                'new_users_this_month' => $newUsersThisMonth,
                'active_subscribers' => $activeSubscribers,
                'active_trials' => $activeTrials,
                'total_mitra' => $totalMitra,
                'total_views' => $totalViews,
                'likes_count' => $likesCount,
                'comments_count' => $commentsCount,
                'trend_data' => $trendData,
                'top_research' => $topResearch,
                'top_authors' => $topAuthorsQuery,
                'heatmap' => $heatmapQuery,
                'mrr' => $mrr,
            ],
            'recent_posts' => Post::with('category')->latest()->take(5)->get(),
        ]);
    }
}
