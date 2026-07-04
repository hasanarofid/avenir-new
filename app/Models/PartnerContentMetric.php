<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerContentMetric extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'period_month' => 'date',
        'read_completion_rate' => 'decimal:2',
        'retention_score' => 'decimal:4',
        'editorial_score' => 'decimal:4',
        'final_score' => 'decimal:4',
    ];

    public function partner()
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_id');
    }
}
