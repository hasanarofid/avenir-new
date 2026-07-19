<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_migrated',
        'profile_photo_path',
        'google_id',
        'google2fa_secret',
        'recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'profile_photo_url',
        'is_subscriber',
    ];

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
                    ? Storage::url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
    }

    protected function defaultProfilePhotoUrl()
    {
        return asset('favicon.png');
    }

    /**
     * Determine if the user is a subscriber with active premium.
     *
     * @return bool
     */
    public function getIsSubscriberAttribute()
    {
        return $this->hasActivePremium();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function hasActivePremium()
    {
        $profile = \Illuminate\Support\Facades\DB::table('user_profiles')
            ->where('user_id', $this->id)
            ->first();

        if ($profile && $profile->is_subscriber) {
            if ($profile->subscription_ends_at) {
                return \Carbon\Carbon::parse($profile->subscription_ends_at)->isFuture();
            }
            return true;
        }

        // Fallback to checking payment_submissions
        $activeSub = $this->subscriptions()->latest('verified_at')->first();
        if (!$activeSub) return false;

        if ($activeSub->verified_at && $activeSub->durasi_hari) {
            $expiresAt = \Carbon\Carbon::parse($activeSub->verified_at)->addDays($activeSub->durasi_hari);
            return now()->lessThan($expiresAt);
        }

        return true;
    }

    public function subscriptions()
    {
        return $this->hasMany(PaymentSubmission::class, 'user_id')->where('status', 'verified');
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function researches()
    {
        return $this->hasMany(Research::class, 'author_id');
    }

    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'recovery_codes' => 'array',
        ];
    }
}
