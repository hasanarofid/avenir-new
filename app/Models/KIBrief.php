<?php

namespace App\Models;

use App\Events\KIBriefPublished;
use Illuminate\Database\Eloquent\Model;

class KIBrief extends Model
{
    protected $table = 'ki_briefs';
    protected $guarded = [];

    protected $casts = [
        'key_numbers' => 'array',
    ];

    public function disclosure()
    {
        return $this->belongsTo(Disclosure::class);
    }

    // Helper method to publish and dispatch event
    public function publish()
    {
        // Assuming we have a 'status' field or we just dispatch on creation
        KIBriefPublished::dispatch($this);
    }
}
