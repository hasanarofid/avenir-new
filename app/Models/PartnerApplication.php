<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerApplication extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'sector_specializations' => 'array',
        'certifications' => 'array',
        'has_research_experience' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->hasOne(PartnerProfile::class, 'application_id');
    }
}
