<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticker extends Model
{
    protected $guarded = [];

    protected $casts = [
        'company_profile' => 'array',
        'financial_highlights' => 'array',
        'financial_ratios' => 'array',
        'main_risks' => 'array',
        'business_segments' => 'array',
        'competitive_advantage' => 'array',
        'key_risks' => 'array',
        'tanggal_listing' => 'date',
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }

    public function disclosures()
    {
        return $this->hasMany(Disclosure::class);
    }

    public function documents()
    {
        return $this->hasMany(EmitenDocument::class, 'kode', 'symbol');
    }

    public function aiDrafts()
    {
        return $this->hasMany(EmitenAIDraft::class, 'kode', 'symbol');
    }

    public function financials()
    {
        return $this->hasMany(EmitenFinancial::class, 'kode', 'symbol');
    }

    public function bankMetrics()
    {
        return $this->hasMany(EmitenBankMetric::class, 'kode', 'symbol');
    }

    public function stockPrices()
    {
        return $this->hasMany(StockPrice::class, 'kode', 'symbol');
    }
}
