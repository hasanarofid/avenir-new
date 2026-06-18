<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'is_migrated', 'profile_photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuids;

    protected $appends = [
        'profile_photo_url',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function hasActivePremium()
    {
        // For MVP, if they have an approved subscription, they are premium.
        // We can check if verified_at + durasi_hari is greater than now().
        $activeSub = $this->subscriptions()->where('status', 'approved')->first();
        if (!$activeSub) return false;

        if ($activeSub->verified_at && $activeSub->durasi_hari) {
            $expiresAt = \Carbon\Carbon::parse($activeSub->verified_at)->addDays($activeSub->durasi_hari);
            return now()->lessThan($expiresAt);
        }

        return true;
    }
    public function subscriptions()
    {
        return $this->hasMany(PaymentSubmission::class, 'user_id')->where('status', 'approved');
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
        ];
    }
}
