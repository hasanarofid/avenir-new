<?php

namespace App\Services;

use App\Models\EmitenFinancial;
use App\Models\EmitenBankMetric;
use App\Models\StockPrice;

class CalculationEngineService
{
    /**
     * Calculate Trailing Twelve Months (TTM) for a specific financial record.
     * TTM = (Current Quarter YTD) + (Last Year Annual) - (Last Year Quarter YTD)
     */
    public function calculateTTM(EmitenFinancial $currentQuarterYTD, EmitenFinancial $lastYearAnnual, EmitenFinancial $lastYearQuarterYTD): EmitenFinancial
    {
        $ttm = new EmitenFinancial();
        $ttm->kode = $currentQuarterYTD->kode;
        $ttm->period_type = 'TTM';
        $ttm->period = $currentQuarterYTD->period;
        $ttm->year = $currentQuarterYTD->year;
        $ttm->quarter = $currentQuarterYTD->quarter;
        $ttm->currency = $currentQuarterYTD->currency;
        $ttm->unit = $currentQuarterYTD->unit;

        $fields = [
            'revenue', 'gross_profit', 'operating_profit', 'ebitda', 
            'profit_before_tax', 'net_profit', 'net_profit_parent', 
            'operating_cash_flow', 'investing_cash_flow', 'financing_cash_flow', 
            'capex', 'free_cash_flow'
        ];

        foreach ($fields as $field) {
            if ($currentQuarterYTD->$field !== null && $lastYearAnnual->$field !== null && $lastYearQuarterYTD->$field !== null) {
                $ttm->$field = $currentQuarterYTD->$field + $lastYearAnnual->$field - $lastYearQuarterYTD->$field;
            }
        }

        // EPS calculation
        if ($currentQuarterYTD->eps !== null && $lastYearAnnual->eps !== null && $lastYearQuarterYTD->eps !== null) {
            $ttm->eps = $currentQuarterYTD->eps + $lastYearAnnual->eps - $lastYearQuarterYTD->eps;
        }

        // Balance sheet items are point-in-time, we just take the current quarter's values
        $bsFields = [
            'total_assets', 'cash_and_equivalents', 'inventory', 
            'total_liabilities', 'interest_bearing_debt', 'total_equity', 'bvps'
        ];

        foreach ($bsFields as $field) {
            $ttm->$field = $currentQuarterYTD->$field;
        }

        return $ttm;
    }

    /**
     * Calculate derived metrics like Margins, PE, PBV, ROE etc. based on current price.
     */
    public function calculateRatios(EmitenFinancial $financial, ?StockPrice $latestPrice)
    {
        $ratios = [];

        // Margins
        if ($financial->revenue > 0) {
            $ratios['gross_margin'] = ($financial->gross_profit / $financial->revenue) * 100;
            $ratios['operating_margin'] = ($financial->operating_profit / $financial->revenue) * 100;
            $ratios['net_margin'] = ($financial->net_profit / $financial->revenue) * 100;
        }

        // ROE & ROA (Requires annualized profit or TTM profit)
        if ($financial->total_equity > 0) {
            $ratios['roe'] = ($financial->net_profit_parent / $financial->total_equity) * 100;
        }
        
        if ($financial->total_assets > 0) {
            $ratios['roa'] = ($financial->net_profit / $financial->total_assets) * 100;
        }

        // Valuation (if price is available)
        if ($latestPrice && $financial->eps > 0) {
            $ratios['pe_ratio'] = $latestPrice->close / $financial->eps;
        }

        if ($latestPrice && $financial->bvps > 0) {
            $ratios['pbv_ratio'] = $latestPrice->close / $financial->bvps;
        }

        return $ratios;
    }

    /**
     * Calculate Year-over-Year (YoY) Growth
     */
    public function calculateYoY(EmitenFinancial $current, EmitenFinancial $previousYear)
    {
        $yoy = [];
        $fields = [
            'revenue', 'gross_profit', 'operating_profit', 'net_profit', 'eps', 'total_assets', 'total_equity'
        ];

        foreach ($fields as $field) {
            if ($previousYear->$field > 0 && $current->$field !== null) {
                $yoy[$field . '_yoy'] = (($current->$field - $previousYear->$field) / abs($previousYear->$field)) * 100;
            }
        }

        return $yoy;
    }
}
