<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Mitra\MitraPayoutService;
use App\Models\PartnerPayoutPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\PartnerProfile;

class MitraPayoutTest extends TestCase
{
    use RefreshDatabase;

    protected MitraPayoutService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MitraPayoutService();
    }

    public function test_calculate_mitra_pool()
    {
        // 20% of 1,000,000 = 200,000
        $this->assertEquals(200000.0, $this->service->calculateMitraPool(1000000.0, 0.20));
        
        // 15% of 1,000,000 = 150,000
        $this->assertEquals(150000.0, $this->service->calculateMitraPool(1000000.0, 0.15));
    }

    public function test_calculate_partner_payout()
    {
        // Partner Score: 50, Total Score: 200 (25%)
        // Pool Amount: 200,000
        // Payout = 25% of 200,000 = 50,000
        $this->assertEquals(50000.0, $this->service->calculatePartnerPayout(50, 200, 200000));
        
        // Division by zero safeguard
        $this->assertEquals(0.0, $this->service->calculatePartnerPayout(50, 0, 200000));
    }

    public function test_process_payout_period()
    {
        $period = PartnerPayoutPeriod::create([
            'period_month' => '2026-07-01',
            'net_subscription_revenue' => 10000000, // 10 Million
            'pool_rate' => 0.20, // 20%
        ]);

        $partner1 = PartnerProfile::create([
            'display_name' => 'Partner A'
        ]);

        $partner2 = PartnerProfile::create([
            'display_name' => 'Partner B'
        ]);

        // Partner scores
        $partnerScores = [
            $partner1->id => 600,
            $partner2->id => 400,
        ];
        
        // Total score = 1000. 
        // Pool amount = 20% * 10,000,000 = 2,000,000
        // Partner 1 gets 60% = 1,200,000
        // Partner 2 gets 40% = 800,000

        $processedPeriod = $this->service->processPayoutPeriod($period, $partnerScores);

        $this->assertEquals(2000000.0, $processedPeriod->mitra_pool_amount);
        $this->assertEquals(1000, $processedPeriod->total_partner_score);
        $this->assertEquals('Calculated', $processedPeriod->status);

        $payouts = $processedPeriod->payouts()->get();
        $this->assertCount(2, $payouts);

        $payout1 = $payouts->where('partner_id', $partner1->id)->first();
        $this->assertEquals(1200000.0, $payout1->payout_amount);

        $payout2 = $payouts->where('partner_id', $partner2->id)->first();
        $this->assertEquals(800000.0, $payout2->payout_amount);
    }
}
