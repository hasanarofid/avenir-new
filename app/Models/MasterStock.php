<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterStock extends Model
{
    use HasFactory;

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'sector',
        'sub_industry_code',
        'sub_industry',
        'is_sharia',
        'logo_url',
        'fiscal_year_end',
        'fs_date',
    ];

    protected $casts = [
        'is_sharia' => 'boolean',
    ];

    /** Scope: filter by sector */
    public function scopeBySector($query, string $sector)
    {
        return $query->where('sector', $sector);
    }

    /** Kembalikan array ['code' => [...data]] — cache-friendly untuk ownership parser */
    public static function toMap(array $codes = []): array
    {
        $q = static::select('code', 'name', 'sector', 'sub_industry_code', 'sub_industry', 'is_sharia', 'logo_url');
        if ($codes) {
            $q->whereIn('code', $codes);
        }
        return $q->get()->keyBy('code')->toArray();
    }
}

