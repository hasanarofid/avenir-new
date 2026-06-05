<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ResearchDocument extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id', 'file_name', 'file_path', 'extracted_text'
    ];

    public function project()
    {
        return $this->belongsTo(ResearchProject::class, 'project_id');
    }
}
