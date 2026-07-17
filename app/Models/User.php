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
     *
     * SEC-M3: Les codes sont stockés sous forme de hachés SHA-256 en base.
     * Le code en clair soumis par l'utilisateur est haché à la volée pour
     * la comparaison via hash_equals() (protection contre les timing attacks).
     * Les codes en clair ne sont affichés qu'une seule fois lors de la
     * génération (TwoFactorController::enable / regenerateCodes).
     */
    public function useRecoveryCode(string $code): bool
    {
        $code     = strtoupper(trim($code));
        $codeHash = hash('sha256', $code);
        $codes    = $this->two_factor_recovery_codes ?? [];

        // Parcours complet avec hash_equals() pour résistance aux timing attacks.
        $found     = false;
        $remaining = [];
        foreach ($codes as $storedHash) {
            if (!$found && hash_equals($storedHash, $codeHash)) {
                $found = true; // code consommé — ne pas le conserver
            } else {
                $remaining[] = $storedHash;
            }
        }

        if (!$found) return false;

        // Consommer le code (usage unique)
        $this->updateQuietly(['two_factor_recovery_codes' => $remaining]);

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

    /**
     * IDs des comptes professionnels de l'utilisateur (cloisonnement multi-tenant).
     * Source unique utilisée par les Policies et les scopes de liste admin.
     *
     * @return array<int, int>
     */
    public function professionalAccountIds(): array
    {
        return $this->professionalAccounts()
            ->pluck('professional_accounts.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function totalXp(): int
    {
        return (int) $this->gamificationProgress()->sum('xp_total');
    }
}
