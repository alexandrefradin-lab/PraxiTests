<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProfessionalAccounts;
use App\Models\Concerns\HasCandidatePurchases;
use App\Models\Concerns\HasGamification;
use App\Models\Concerns\HasGrimoire;
use App\Models\Concerns\HasTwoFactorAuthentication;
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

/**
 * Identité et authentification. Les responsabilités périphériques vivent dans
 * des concerns dédiés (refactor god-model, phase 1) :
 *  - 2FA               → Concerns\HasTwoFactorAuthentication
 *  - gamification      → Concerns\HasGamification
 *  - grimoire          → Concerns\HasGrimoire
 *  - multi-tenant      → Concerns\BelongsToProfessionalAccounts
 * Comportement figé par tests/Unit/Models/UserModelTest.php (phase 0).
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Billable, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;
    use BelongsToProfessionalAccounts, HasCandidatePurchases, HasGamification, HasGrimoire, HasTwoFactorAuthentication;

    protected $fillable = [
        'name', 'email', 'password', 'locale', 'ui_theme', 'avatar_path',
        'terms_accepted_at', 'terms_version',
        // SEC-C1/C2: two_factor_secret, two_factor_recovery_codes, last_login_at, last_login_ip
        // are intentionally excluded from $fillable to prevent mass-assignment attacks.
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'last_login_ip', 'terms_accepted_at'];

    protected $casts = [
        'email_verified_at'          => 'datetime',
        'last_login_at'              => 'datetime',
        'terms_accepted_at'          => 'datetime',
        'password'                   => 'hashed',
        // SEC-M3 : secret TOTP chiffré au repos (via APP_KEY). Une exfiltration
        // de la base ne livre plus les secrets 2FA en clair. Migration de
        // chiffrement des secrets existants : 2026_07_16_120001_encrypt_two_factor_secret.
        'two_factor_secret'          => 'encrypted',
        'two_factor_recovery_codes'  => 'array',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }
}
