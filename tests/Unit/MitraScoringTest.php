<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Mitra\MitraScoringEngine;

class MitraScoringTest extends TestCase
{
    protected MitraScoringEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new MitraScoringEngine();
    }

    public function test_get_reader_weight()
    {
        $this->assertEquals(0.0, $this->engine->getReaderWeight('Anonymous visitor'));
        $this->assertEquals(0.1, $this->engine->getReaderWeight('Registered free user'));
        $this->assertEquals(0.5, $this->engine->getReaderWeight('Free trial user'));
        $this->assertEquals(1.0, $this->engine->getReaderWeight('Paid monthly subscriber'));
        $this->assertEquals(1.5, $this->engine->getReaderWeight('Paid annual subscriber'));
        $this->assertEquals(2.0, $this->engine->getReaderWeight('Institutional subscriber'));
        $this->assertEquals(0.0, $this->engine->getReaderWeight('Unknown'));
    }

    public function test_get_content_multiplier()
    {
        $this->assertEquals(0.3, $this->engine->getContentMultiplier('News Free'));
        $this->assertEquals(0.8, $this->engine->getContentMultiplier('KI Brief'));
        $this->assertEquals(2.0, $this->engine->getContentMultiplier('Sector Deep Dive'));
        $this->assertEquals(1.0, $this->engine->getContentMultiplier('Unknown'));
    }

    public function test_get_partner_level_multiplier()
    {
        $this->assertEquals(0.5, $this->engine->getPartnerLevelMultiplier('Contributor'));
        $this->assertEquals(1.0, $this->engine->getPartnerLevelMultiplier('Verified Mitra Analyst'));
        $this->assertEquals(1.25, $this->engine->getPartnerLevelMultiplier('Avenir Select Analyst'));
        $this->assertEquals(1.5, $this->engine->getPartnerLevelMultiplier('Lead Sector Contributor'));
    }

    public function test_calculate_final_score()
    {
        // 40% Qualified Reads + 25% Editorial Quality + 20% Retention & Utility + 10% Conversion Impact + 5% Timeliness
        // Base Score = (100 * 0.40) + (100 * 0.25) + (100 * 0.20) + (100 * 0.10) + (100 * 0.05) = 100
        // Multiply by Content ('Artikel Premium' = 1.0) and Level ('Verified Mitra Analyst' = 1.0)
        // Final Score = 100 * 1.0 * 1.0 = 100
        
        $finalScore = $this->engine->calculateFinalScore(
            100, // qualifiedReadsScore
            100, // editorialQualityScore
            100, // retentionUtilityScore
            100, // conversionImpactScore
            100, // timelinessScore
            'Artikel Premium',
            'Verified Mitra Analyst'
        );
        
        $this->assertEquals(100.0, $finalScore);

        // Test with different multipliers
        // Base Score = 100
        // Content ('Equity Research' = 1.8) 
        // Level ('Lead Sector Contributor' = 1.5)
        // Final Score = 100 * 1.8 * 1.5 = 270
        $finalScore2 = $this->engine->calculateFinalScore(100, 100, 100, 100, 100, 'Equity Research', 'Lead Sector Contributor');
        
        $this->assertEquals(270.0, $finalScore2);
    }
}
