<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPayoutPeriod extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'period_month' => 'date',
        'gross_subscription_revenue' => 'decimal:2',
        'net_subscription_revenue' => 'decimal:2',
        'pool_rate' => 'decimal:4',
        'mitra_pool_amount' => 'decimal:2',
        'total_partner_score' => 'decimal:4',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function payouts()
    {
        return $this->hasMany(PartnerPayout::class, 'payout_period_id');
    }
}
