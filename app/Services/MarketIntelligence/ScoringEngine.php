<?php

namespace App\Services\MarketIntelligence;

use App\Models\MarketStanceDaily;
use App\Models\SectorBiasDaily;
use App\Models\DeskBrief;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScoringEngine
{
    /**
     * Hitung Regime Score berdasarkan bobot PRD.
     * regime = 0.25*flow + 0.20*breadth + 0.15*momentum + 0.15*rupiah + 0.15*yield + 0.10*rotasi
     */
    public function calculateRegimeScore(string $date): MarketStanceDaily
    {
        // For MVP, simulate component scores
        // Each component is a normalized z-score mapped to 0-100 scale, so let's just pick random for now
        $flow = rand(30, 80);
        $breadth = rand(30, 80);
        $momentum = rand(30, 80);
        $rupiah = rand(30, 80);
        $yield = rand(30, 80);
        $rotasi = rand(30, 80);

        $totalScore = round(
            (0.25 * $flow) +
            (0.20 * $breadth) +
            (0.15 * $momentum) +
            (0.15 * $rupiah) +
            (0.15 * $yield) +
            (0.10 * $rotasi)
        );

        $label = $this->getRegimeLabel($totalScore);

        $stance = MarketStanceDaily::updateOrCreate(
            ['date' => $date],
            [
                'score' => $totalScore,
                'label' => $label,
                'foreign_score' => $flow,
                'breadth_score' => $breadth,
                'momentum_score' => $momentum,
                'rupiah_score' => $rupiah,
                'yield_score' => $yield,
                'sector_score' => $rotasi,
            ]
        );

        return $stance;
    }

    protected function getRegimeLabel(int $score): string
    {
        if ($score >= 70) return 'RISK-ON';
        if ($score >= 40) return 'SELECTIVE RISK-ON';
        if ($score >= 20) return 'DEFENSIVE';
        return 'RISK-OFF';
    }

    /**
     * Hitung Confluence Score untuk setiap sektor di hari tersebut.
     * total = Σ (-4...+4)
     * label = ≥+3 Strong, +2 Building, +1 Watch, mayoritas negatif Avoid
     */
    public function calculateConfluence(string $date)
    {
        $sectors = SectorBiasDaily::whereDate('date', $date)->get();

        foreach ($sectors as $sector) {
            // Scores are between -1, 0, 1
            $total = $sector->rotation_score + $sector->smart_money_score + $sector->valuation_score + $sector->event_score;
            
            $label = 'Avoid';
            if ($total >= 3) {
                $label = 'Strong';
            } elseif ($total == 2) {
                $label = 'Building';
            } elseif ($total == 1) {
                $label = 'Watch';
            }

            $sector->update([
                'confluence_score' => $total,
                'confluence_label' => $label
            ]);
        }
    }
}
