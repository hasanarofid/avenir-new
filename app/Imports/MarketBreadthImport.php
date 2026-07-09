<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MarketBreadthImport implements ToCollection, WithHeadingRow
{
    public $results = [];

    public function collection(Collection $rows)
    {
        $up_gt2_n = 0;
        $up_0_2_n = 0;
        $stable_n = 0;
        $down_0_2_n = 0;
        $down_gt2_n = 0;

        $mcap_up = 0;
        $mcap_down = 0;
        $mcap_stable = 0;

        $value_up = 0;
        $value_down = 0;
        $value_stable = 0;

        foreach ($rows as $row) {
            $prev = $this->parseNumber($this->findValue($row, ['sebelumnya', 'previous', 'prev', 'prev_close']));
            $close = $this->parseNumber($this->findValue($row, ['penutupan', 'close', 'closing_price', 'last']));
            $shares = $this->parseNumber($this->findValue($row, ['listed_shares', 'listed_share', 'saham_tercatat']));
            $value = $this->parseNumber($this->findValue($row, ['nilai', 'value', 'trading_value']));

            if (!$prev || !$close || !$shares || $value === null) {
                continue;
            }

            if ($prev <= 0 || $close <= 0 || $shares <= 0 || $value < 0) {
                continue;
            }

            $return_pct = ($close / $prev) - 1;
            $market_cap = $close * $shares;
            $trading_value = $value;

            if ($return_pct < -0.02) {
                $down_gt2_n++;
                $mcap_down += $market_cap;
                $value_down += $trading_value;
            } elseif ($return_pct < 0 && $return_pct >= -0.02) {
                $down_0_2_n++;
                $mcap_down += $market_cap;
                $value_down += $trading_value;
            } elseif ($return_pct == 0) {
                $stable_n++;
                $mcap_stable += $market_cap;
                $value_stable += $trading_value;
            } elseif ($return_pct > 0 && $return_pct <= 0.02) {
                $up_0_2_n++;
                $mcap_up += $market_cap;
                $value_up += $trading_value;
            } elseif ($return_pct > 0.02) {
                $up_gt2_n++;
                $mcap_up += $market_cap;
                $value_up += $trading_value;
            }
        }

        $advancers = $up_0_2_n + $up_gt2_n;
        $decliners = $down_0_2_n + $down_gt2_n;
        $total_active = $advancers + $decliners;
        $total_stocks = $total_active + $stable_n;

        $ad_score = $this->safeDiv($advancers, $advancers + $decliners) * 100;
        $strong_movers_score = $this->safeDiv($up_gt2_n, $up_gt2_n + $down_gt2_n) * 100;
        $mcap_breadth_score = $this->safeDiv($mcap_up, $mcap_up + $mcap_down) * 100;
        $value_breadth_score = $this->safeDiv($value_up, $value_up + $value_down) * 100;
        $active_participation_score = $this->safeDiv($total_active, $total_stocks) * 100;

        $market_breadth_score_raw = (
            0.30 * $ad_score +
            0.20 * $strong_movers_score +
            0.20 * $mcap_breadth_score +
            0.20 * $value_breadth_score +
            0.10 * $active_participation_score
        );

        $this->results = [
            "advancers" => $advancers,
            "decliners" => $decliners,
            "stable" => $stable_n,
            "market_breadth_score" => (int) round($market_breadth_score_raw)
        ];
    }

    private function findValue($row, $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key])) return $row[$key];
        }
        return null;
    }

    private function parseNumber($val)
    {
        if ($val === null || $val === '' || $val === '-') return null;
        $val = str_replace([',', ' '], '', (string)$val);
        return (float) $val;
    }

    private function safeDiv($a, $b)
    {
        return $b > 0 ? $a / $b : 0;
    }
}
