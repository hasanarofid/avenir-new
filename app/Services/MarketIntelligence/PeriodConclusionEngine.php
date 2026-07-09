<?php

namespace App\Services\MarketIntelligence;

use App\Models\MarketStanceDaily;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PeriodConclusionEngine
{
    const COMPONENT_LABELS = [
        'momentum_score' => 'price trend',
        'breadth_score' => 'breadth',
        'foreign_score' => 'foreign flow',
        'sector_score' => 'sector rotation',
        'rupiah_score' => 'market stability',
    ];

    public function generateConclusion(string $endDate = null): string
    {
        $endDate = $endDate ? Carbon::parse($endDate) : Carbon::today();
        
        // Let's take the last 30 days of data for the "period"
        $startDate = $endDate->copy()->subDays(30);
        
        $stances = MarketStanceDaily::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();
            
        if ($stances->isEmpty()) {
            return "Insufficient data to generate period conclusion.";
        }
        
        return $this->buildPeriodConclusion($stances);
    }
    
    public function cleanRegimeLabel(?string $label): string
    {
        if (!$label) return "Unknown";
        $label = trim($label);
        
        $map = [
            "Stress / Risk-Off Pressure" => "Stress / Risk-Off Pressure",
            "Risk-Off" => "Risk-Off",
            "Defensive Neutral" => "Defensive Neutral",
            "Neutral Rotation" => "Neutral Rotation",
            "Neutral Rotation / Tactical Rebound" => "Neutral Rotation",
            "Constructive Rotation" => "Constructive Rotation",
            "Risk-On Accumulation" => "Risk-On Accumulation",
        ];
        
        return $map[$label] ?? $label;
    }
    
    public function regimeStateFromScore(float $score): string
    {
        if ($score < 35) return "stress conditions";
        if ($score < 45) return "risk-off pressure";
        if ($score < 55) return "a defensive neutral phase";
        if ($score < 65) return "a tactical recovery attempt";
        if ($score < 75) return "a constructive rotation phase";
        return "a risk-on accumulation phase";
    }
    
    public function monthPhase(Carbon $date): string
    {
        $day = $date->day;
        $month = $date->format('F'); // Full month name
        
        if ($day <= 10) {
            $phase = "early";
        } elseif ($day <= 20) {
            $phase = "mid";
        } else {
            $phase = "late";
        }
        
        return "{$phase} {$month}";
    }
    
    public function formatDate(Carbon $date): string
    {
        return $date->format('j F');
    }
    
    public function getComponentColumns(Collection $stances): array
    {
        return [
            'momentum_score',
            'breadth_score',
            'foreign_score',
            'sector_score',
            'rupiah_score',
        ];
    }
    
    public function topSupports(MarketStanceDaily $row, float $minScore = 55, int $maxItems = 3): array
    {
        $cols = $this->getComponentColumns(collect([$row]));
        $items = [];
        
        foreach ($cols as $col) {
            $score = $row->$col;
            if ($score !== null && $score >= $minScore) {
                $items[] = ['col' => $col, 'score' => $score];
            }
        }
        
        usort($items, fn($a, $b) => $b['score'] <=> $a['score']);
        
        if (empty($items)) {
            // fallback: top 3
            foreach ($cols as $col) {
                if ($row->$col !== null) {
                    $items[] = ['col' => $col, 'score' => $row->$col];
                }
            }
            usort($items, fn($a, $b) => $b['score'] <=> $a['score']);
        }
        
        $items = array_slice($items, 0, $maxItems);
        return array_map(fn($item) => self::COMPONENT_LABELS[$item['col']], $items);
    }
    
    public function breakdownComponents(MarketStanceDaily $peakRow, MarketStanceDaily $lastRow, int $maxItems = 3): array
    {
        $cols = $this->getComponentColumns(collect([$lastRow]));
        $broken = [];
        
        foreach ($cols as $col) {
            $peakScore = $peakRow->$col;
            $lastScore = $lastRow->$col;
            
            if ($peakScore === null || $lastScore === null) continue;
            
            $drop = $peakScore - $lastScore;
            
            if ($lastScore < 35 || ($drop >= 20 && $lastScore < 50)) {
                $broken[] = ['col' => $col, 'drop' => $drop, 'lastScore' => $lastScore];
            }
        }
        
        // sort by lastScore ASC, then drop DESC
        usort($broken, function($a, $b) {
            if ($a['lastScore'] === $b['lastScore']) {
                return $b['drop'] <=> $a['drop'];
            }
            return $a['lastScore'] <=> $b['lastScore'];
        });
        
        $broken = array_slice($broken, 0, $maxItems);
        return array_map(fn($item) => self::COMPONENT_LABELS[$item['col']], $broken);
    }
    
    public function detectPersistentDrag(Collection $stances): ?string
    {
        // Flow priority
        $flowScores = $stances->pluck('foreign_score')->filter(fn($v) => $v !== null);
        if ($flowScores->isNotEmpty()) {
            $flowAvg = $flowScores->average();
            $flowMax = $flowScores->max();
            if ($flowAvg < 45 || $flowMax < 50) {
                return "foreign flow never confirmed the rebound";
            }
        }
        
        $weakComponents = [];
        foreach ($this->getComponentColumns($stances) as $col) {
            $scores = $stances->pluck($col)->filter(fn($v) => $v !== null);
            if ($scores->isNotEmpty()) {
                $avg = $scores->average();
                $max = $scores->max();
                if ($avg < 45 && $max < 55) {
                    $weakComponents[] = ['col' => $col, 'avg' => $avg];
                }
            }
        }
        
        if (!empty($weakComponents)) {
            usort($weakComponents, fn($a, $b) => $a['avg'] <=> $b['avg']);
            $label = self::COMPONENT_LABELS[$weakComponents[0]['col']];
            return "{$label} never fully confirmed the move";
        }
        
        return null;
    }
    
    public function joinItems(array $items): string
    {
        if (empty($items)) return "";
        if (count($items) === 1) return $items[0];
        if (count($items) === 2) return "{$items[0]} and {$items[1]}";
        
        $last = array_pop($items);
        return implode(", ", $items) . ", and {$last}";
    }
    
    public function buildPeriodConclusion(Collection $df): string
    {
        $startRow = $df->first();
        $lastRow = $df->last();
        
        $peakRow = $df->sortByDesc('score')->first();
        $troughRow = $df->sortBy('score')->first();
        
        $startDate = Carbon::parse($startRow->date);
        $lastDate = Carbon::parse($lastRow->date);
        $peakDate = Carbon::parse($peakRow->date);
        $troughDate = Carbon::parse($troughRow->date);
        
        $startScore = (float) $startRow->score;
        $lastScore = (float) $lastRow->score;
        $peakScore = (float) $peakRow->score;
        $troughScore = (float) $troughRow->score;
        
        // Pre-peak low
        $prePeak = $df->filter(fn($row) => Carbon::parse($row->date)->lte($peakDate));
        $prePeakLow = $prePeak->sortBy('score')->first();
        
        $initialState = $this->regimeStateFromScore((float) $prePeakLow->score);
        $initialPeriod = $this->monthPhase(Carbon::parse($prePeakLow->date));
        
        $recoveryState = $this->regimeStateFromScore($peakScore);
        $recoveryPeriod = $this->monthPhase($peakDate);
        
        $supports = $this->topSupports($peakRow);
        $supportText = $this->joinItems($supports);
        
        $persistentDrag = $this->detectPersistentDrag($df);
        
        $broken = $this->breakdownComponents($peakRow, $lastRow);
        $brokenText = $this->joinItems($broken);
        
        $lastLabel = $this->cleanRegimeLabel($lastRow->label);
        
        // Sentence 1
        $sentence1 = "The market moved from {$initialState} in {$initialPeriod} into {$recoveryState} in {$recoveryPeriod}.";
        
        // Sentence 2
        if ($supportText) {
            $sentence2 = "The strongest confirmation appeared on {$this->formatDate($peakDate)}, supported by {$supportText}.";
        } else {
            $sentence2 = "The strongest confirmation appeared on {$this->formatDate($peakDate)}.";
        }
        
        // Sentence 3
        if ($persistentDrag) {
            $sentence3 = "However, {$persistentDrag}.";
        } else {
            $sentence3 = "However, confirmation was not strong enough to sustain a full regime upgrade.";
        }
        
        // Sentence 4
        if ($lastScore <= $peakScore - 15 && $lastScore < 45) {
            if ($brokenText) {
                $sentence4 = "On {$this->formatDate($lastDate)}, {$brokenText} broke down sharply, pushing the regime back into {$lastLabel}.";
            } else {
                $sentence4 = "On {$this->formatDate($lastDate)}, the regime deteriorated sharply, pushing the market back into {$lastLabel}.";
            }
        } elseif ($lastScore < $peakScore - 10) {
            $sentence4 = "By {$this->formatDate($lastDate)}, the regime had weakened from its peak, ending the period in {$lastLabel}.";
        } else {
            $sentence4 = "By {$this->formatDate($lastDate)}, the regime remained in {$lastLabel}.";
        }
        
        return trim("{$sentence1} {$sentence2} {$sentence3} {$sentence4}");
    }
}
