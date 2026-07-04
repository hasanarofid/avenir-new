<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPayout extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'partner_score' => 'decimal:4',
        'payout_amount' => 'decimal:2',
        'rollover_amount' => 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(PartnerPayoutPeriod::class, 'payout_period_id');
    }

    public function partner()
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_id');
    }
}
