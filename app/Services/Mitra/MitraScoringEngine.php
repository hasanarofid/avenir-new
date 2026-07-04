<?php

namespace App\Services\Mitra;

use App\Models\PartnerProfile;

class MitraScoringEngine
{
    /**
     * Get weight multiplier based on reader type.
     */
    public function getReaderWeight(string $readerType): float
    {
        return match ($readerType) {
            'Anonymous visitor' => 0.0,
            'Registered free user' => 0.1,
            'Free trial user' => 0.5,
            'Paid monthly subscriber' => 1.0,
            'Paid annual subscriber' => 1.5,
            'Institutional subscriber' => 2.0,
            default => 0.0,
        };
    }

    /**
     * Get content multiplier based on content type.
     */
    public function getContentMultiplier(string $contentType): float
    {
        return match ($contentType) {
            'News Free' => 0.3,
            'Market Note' => 0.5,
            'KI Brief' => 0.8,
            'Artikel Premium' => 1.0,
            'Bedah Pubex' => 1.2,
            'Equity Research' => 1.8,
            'Sector Deep Dive' => 2.0,
            'Valuation Model' => 2.2,
            'Downloadable File' => 2.2,
            default => 1.0,
        };
    }

    /**
     * Get partner level multiplier.
     */
    public function getPartnerLevelMultiplier(string $analystLevel): float
    {
        return match ($analystLevel) {
            'Contributor' => 0.5,
            'Verified Mitra Analyst' => 1.0,
            'Avenir Select Analyst' => 1.25,
            'Lead Sector Contributor' => 1.5,
            default => 0.5,
        };
    }

    /**
     * Calculate Final Score based on formula:
     * 40% Qualified Reads + 25% Editorial Quality + 20% Retention & Utility + 10% Conversion Impact + 5% Timeliness
     * Multiplied by Content Multiplier and Partner Level Multiplier.
     */
    public function calculateFinalScore(
        float $qualifiedReadsScore,
        float $editorialQualityScore,
        float $retentionUtilityScore,
        float $conversionImpactScore,
        float $timelinessScore,
        string $contentType,
        string $analystLevel
    ): float {
        $baseScore = ($qualifiedReadsScore * 0.40) +
                     ($editorialQualityScore * 0.25) +
                     ($retentionUtilityScore * 0.20) +
                     ($conversionImpactScore * 0.10) +
                     ($timelinessScore * 0.05);

        $contentMult = $this->getContentMultiplier($contentType);
        $levelMult = $this->getPartnerLevelMultiplier($analystLevel);

        return $baseScore * $contentMult * $levelMult;
    }
}
