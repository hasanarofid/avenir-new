<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ResearchProject extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ticker', 'title', 'prompt', 'status', 'created_by'
    ];

    public function documents()
    {
        return $this->hasMany(ResearchDocument::class, 'project_id');
    }

    public function drafts()
    {
        return $this->hasMany(ResearchDraft::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
