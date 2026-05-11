<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'locale', 'avatar_path'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')->withPivot(['earned_at', 'context']);
    }

    public function gamificationProgress(): HasMany
    {
        return $this->hasMany(GamificationProgress::class);
    }

    public function professionalAccounts()
    {
        return $this->belongsToMany(ProfessionalAccount::class, 'professional_account_users')
            ->withPivot('role')->withTimestamps();
    }

    public function totalXp(): int
    {
        return (int) $this->gamificationProgress()->sum('xp_total');
    }
}
