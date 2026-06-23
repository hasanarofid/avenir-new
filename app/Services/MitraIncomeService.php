<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MitraIncomeService
{
    // Tier percentages for rank 1 to 10
    const TIER_PERCENTAGES = [
        1 => 0.25,
        2 => 0.20,
        3 => 0.15,
        4 => 0.10,
        5 => 0.08,
        6 => 0.07,
        7 => 0.06,
        8 => 0.05,
        9 => 0.03,
        10 => 0.01,
    ];

    /**
     * Get the monthly pool budget
     */
    public function getMonthlyPool(int $month, int $year): float
    {
        $poolConfig = DB::table('pool_config')
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->first();
            
        return $poolConfig ? (float) $poolConfig->pool_budget_idr : 0;
    }

    /**
     * Calculate monthly income for a specific Mitra
     */
    public function calculateMonthlyIncome(string|int $userId, int $month, int $year): float
    {
        $poolAmount = $this->getMonthlyPool($month, $year);
        if ($poolAmount <= 0) {
            return 0;
        }

        $ranking = $this->getMonthlyRanking($month, $year);
        
        $userRank = null;
        foreach ($ranking as $index => $partner) {
            if ($partner['user_id'] == $userId) {
                $userRank = $index + 1; // 1-based index
                break;
            }
        }

        if ($userRank && isset(self::TIER_PERCENTAGES[$userRank])) {
            return $poolAmount * self::TIER_PERCENTAGES[$userRank];
        }

        return 0;
    }

    /**
     * Calculate cumulative income for a specific Mitra up to current month
     */
    public function calculateCumulativeIncome(string|int $userId): float
    {
        // To accurately calculate cumulative, we need to iterate over all past pool configurations
        $pastPools = DB::table('pool_config')->orderBy('period_year', 'asc')->orderBy('period_month', 'asc')->get();
        $total = 0;
        
        foreach ($pastPools as $pool) {
            $total += $this->calculateMonthlyIncome($userId, $pool->period_month, $pool->period_year);
        }

        return $total;
    }

    /**
     * Get all verified partners and their rank based on total views
     */
    public function getMonthlyRanking(int $month, int $year): array
    {
        $partnersData = DB::table('partners')
            ->join('users', 'partners.user_id', '=', 'users.id')
            ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->where('partners.is_verified', true)
            ->select(
                'users.id as user_id',
                'users.name',
                'partners.certification',
                'partners.specializations',
                'user_profiles.first_name',
                'user_profiles.last_name'
            )
            ->get();

        $partnerIds = $partnersData->pluck('user_id')->toArray();
        
        if (empty($partnerIds)) {
            return [];
        }

        $researchViews = DB::table('research_view_logs')
            ->join('research', 'research_view_logs.research_id', '=', 'research.id')
            ->whereIn('research.author_id', $partnerIds)
            ->whereMonth('research_view_logs.created_at', $month)
            ->whereYear('research_view_logs.created_at', $year)
            ->select('research.author_id as user_id', DB::raw('count(*) as total_views'))
            ->groupBy('research.author_id')
            ->get()->keyBy('user_id');

        $articleViews = DB::table('article_view_logs')
            ->join('articles', 'article_view_logs.article_id', '=', 'articles.id')
            ->whereIn('articles.user_id', $partnerIds)
            ->whereMonth('article_view_logs.created_at', $month)
            ->whereYear('article_view_logs.created_at', $year)
            ->select('articles.user_id as user_id', DB::raw('count(*) as total_views'))
            ->groupBy('articles.user_id')
            ->get()->keyBy('user_id');

        $ranking = $partnersData->map(function ($p) use ($researchViews, $articleViews) {
            $rViews = $researchViews->has($p->user_id) ? $researchViews->get($p->user_id)->total_views : 0;
            $aViews = $articleViews->has($p->user_id) ? $articleViews->get($p->user_id)->total_views : 0;
            $totalViews = $rViews + $aViews;

            return [
                'user_id' => $p->user_id,
                'name' => $p->name ?? (trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: 'Mitra Analis'),
                'certification' => $p->certification ?? 'Mitra Analis',
                'specializations' => json_decode($p->specializations ?? '[]'),
                'total_views' => $totalViews,
            ];
        })->sortByDesc('total_views')->values()->toArray();

        // Calculate income for each rank
        $poolAmount = $this->getMonthlyPool($month, $year);
        
        $actualRank = 1;
        foreach ($ranking as $index => &$partner) {
            if ($partner['total_views'] > 0) {
                $partner['rank'] = $actualRank;
                $income = 0;
                $percentage = 0;
                if (isset(self::TIER_PERCENTAGES[$actualRank])) {
                    $percentage = self::TIER_PERCENTAGES[$actualRank];
                    $income = $poolAmount * $percentage;
                }
                
                $partner['income'] = $income;
                $partner['tier_percentage'] = $percentage * 100;
                $actualRank++;
            } else {
                $partner['rank'] = null; // No rank if 0 views
                $partner['income'] = 0;
                $partner['tier_percentage'] = 0;
            }
        }

        return $ranking;
    }
}
