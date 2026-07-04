<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerProfile extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'sector_focus' => 'array',
        'payout_enabled' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function application()
    {
        return $this->belongsTo(PartnerApplication::class, 'application_id');
    }

    public function metrics()
    {
        return $this->hasMany(PartnerContentMetric::class, 'partner_id');
    }

    public function payouts()
    {
        return $this->hasMany(PartnerPayout::class, 'partner_id');
    }
}
