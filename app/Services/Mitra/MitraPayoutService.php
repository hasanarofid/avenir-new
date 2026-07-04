<?php

namespace App\Services\Mitra;

use App\Models\PartnerPayoutPeriod;
use App\Models\PartnerPayout;

class MitraPayoutService
{
    /**
     * Calculate Mitra Pool Amount for a given period.
     */
    public function calculateMitraPool(float $netSubscriptionRevenue, float $poolRate = 0.20): float
    {
        return $netSubscriptionRevenue * $poolRate;
    }

    /**
     * Calculate individual partner payout based on score proportion.
     */
    public function calculatePartnerPayout(float $partnerScore, float $totalPartnerScore, float $mitraPoolAmount): float
    {
        if ($totalPartnerScore <= 0) {
            return 0.0;
        }

        return ($partnerScore / $totalPartnerScore) * $mitraPoolAmount;
    }

    /**
     * Process period calculations.
     */
    public function processPayoutPeriod(PartnerPayoutPeriod $period, array $partnerScores)
    {
        // $partnerScores array format: ['partner_id' => score]
        
        $totalScore = array_sum($partnerScores);
        
        $period->update([
            'mitra_pool_amount' => $this->calculateMitraPool($period->net_subscription_revenue, $period->pool_rate),
            'total_partner_score' => $totalScore,
            'status' => 'Calculated',
            'calculated_at' => now(),
        ]);

        foreach ($partnerScores as $partnerId => $score) {
            $payoutAmount = $this->calculatePartnerPayout($score, $totalScore, $period->mitra_pool_amount);
            
            PartnerPayout::updateOrCreate(
                [
                    'payout_period_id' => $period->id,
                    'partner_id' => $partnerId,
                ],
                [
                    'partner_score' => $score,
                    'payout_amount' => $payoutAmount,
                    'payout_status' => 'Pending',
                ]
            );
        }

        return $period;
    }
}
