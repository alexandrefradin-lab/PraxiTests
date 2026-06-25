<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use Billable, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'locale', 'avatar_path',
        'terms_accepted_at', 'terms_version', 'last_login_at', 'last_login_ip',
        'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected $casts = [
        'email_verified_at'          => 'datetime',
        'last_login_at'              => 'datetime',
        'terms_accepted_at'          => 'datetime',
        'password'                   => 'hashed',
        'two_factor_recovery_codes'  => 'array',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // 2FA helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Indique si le 2FA est activé (secret présent). */
    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret);
    }

    /**
     * Vérifie si un code de récupération est valide, et le consomme si oui.
     * Les codes sont stockés en clair (format XXXXXX-XXXXXX) dans le champ JSON.
     */
    public function useRecoveryCode(string $code): bool
    {
        $code  = strtoupper(trim($code));
        $codes = $this->two_factor_recovery_codes ?? [];

        $key = array_search($code, $codes, strict: true);
        if ($key === false) return false;

        // Consommer le code (usage unique)
        array_splice($codes, $key, 1);
        $this->updateQuietly(['two_factor_recovery_codes' => $codes]);

        return true;
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function profileGrimoire(): HasOne
    {
        return $this->hasOne(ProfileGrimoire::class);
    }

    /** Récupère le Grimoire global du candidat, en le créant si absent. */
    public function getOrCreateGrimoire(): ProfileGrimoire
    {
        return $this->profileGrimoire()->firstOrCreate(['user_id' => $this->id]);
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
