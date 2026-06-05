<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ResearchDraft extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id', 'model_used', 'structured_json', 'status'
    ];

    protected $casts = [
        'structured_json' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(ResearchProject::class, 'project_id');
    }
}
