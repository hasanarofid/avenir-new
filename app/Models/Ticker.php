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
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }

    public function disclosures()
    {
        return $this->hasMany(Disclosure::class);
    }
}
