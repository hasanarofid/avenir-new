<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'paket',
        'durasi_hari',
        'nominal',
        'bukti_url',
        'bukti_path',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
        'admin_notes',
        'user_email',
        'user_nama',
    ];
}
