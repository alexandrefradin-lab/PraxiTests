<?php

/**
 * Filet de caractérisation du modèle User (Phase 0 — audit 2026-07-27).
 *
 * User est le nœud de plus grand rayon de souffle du projet (200 arêtes dans le
 * knowledge graph) : auth, 2FA, Stripe, rôles, gamification et cloisonnement
 * multi-tenant y convergent. Ces tests FIGENT le comportement actuel AVANT toute
 * extraction de responsabilités en traits/services, pour qu'un refactor qui change
 * une clé étrangère, une table pivot ou une logique vire immédiatement au rouge.
 *
 * Deux niveaux :
 *  - définition de relations : attrape un déplacement de méthode qui casserait le
 *    câblage Eloquent (mauvaise FK, mauvais related, mauvaise table pivot) ;
 *  - comportement : fige la logique des helpers critiques (2FA, tenancy, XP).
 */

use App\Models\GamificationProgress;
use App\Models\ProfessionalAccount;
use App\Models\ProfileGrimoire;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// ── Câblage des relations (survit à une extraction en trait) ──────────────────

it('câble profile() en HasOne vers Profile sur user_id', function () {
    $rel = User::factory()->create()->profile();
    expect($rel)->toBeInstanceOf(HasOne::class)
        ->and($rel->getForeignKeyName())->toBe('user_id')
        ->and($rel->getRelated())->toBeInstanceOf(\App\Models\Profile::class);
});

it('câble attempts() en HasMany vers TestAttempt sur user_id', function () {
    $rel = User::factory()->create()->attempts();
    expect($rel)->toBeInstanceOf(HasMany::class)
        ->and($rel->getForeignKeyName())->toBe('user_id')
        ->and($rel->getRelated())->toBeInstanceOf(\App\Models\TestAttempt::class);
});

it('câble profileGrimoire() en HasOne vers ProfileGrimoire sur user_id', function () {
    $rel = User::factory()->create()->profileGrimoire();
    expect($rel)->toBeInstanceOf(HasOne::class)
        ->and($rel->getForeignKeyName())->toBe('user_id')
        ->and($rel->getRelated())->toBeInstanceOf(ProfileGrimoire::class);
});

it('câble gamificationProgress() en HasMany vers GamificationProgress sur user_id', function () {
    $rel = User::factory()->create()->gamificationProgress();
    expect($rel)->toBeInstanceOf(HasMany::class)
        ->and($rel->getForeignKeyName())->toBe('user_id')
        ->and($rel->getRelated())->toBeInstanceOf(GamificationProgress::class);
});

it('câble easterEggs() en HasMany vers UserEasterEgg sur user_id', function () {
    $rel = User::factory()->create()->easterEggs();
    expect($rel)->toBeInstanceOf(HasMany::class)
        ->and($rel->getForeignKeyName())->toBe('user_id')
        ->and($rel->getRelated())->toBeInstanceOf(\App\Models\UserEasterEgg::class);
});

it('câble badges() en BelongsToMany via la table pivot user_badges', function () {
    $rel = User::factory()->create()->badges();
    expect($rel)->toBeInstanceOf(BelongsToMany::class)
        ->and($rel->getTable())->toBe('user_badges')
        ->and($rel->getRelated())->toBeInstanceOf(\App\Models\Badge::class);
});

it('câble professionalAccounts() en BelongsToMany via professional_account_users', function () {
    $rel = User::factory()->create()->professionalAccounts();
    expect($rel)->toBeInstanceOf(BelongsToMany::class)
        ->and($rel->getTable())->toBe('professional_account_users')
        ->and($rel->getRelated())->toBeInstanceOf(ProfessionalAccount::class);
});

// ── 2FA : hasTwoFactorEnabled() / useRecoveryCode() ───────────────────────────

it('hasTwoFactorEnabled() reflète la présence du secret', function () {
    $user = User::factory()->create();
    expect($user->hasTwoFactorEnabled())->toBeFalse();

    $user->updateQuietly(['two_factor_secret' => 'JBSWY3DPEHPK3PXP']);
    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

it('useRecoveryCode() consomme un code valide (usage unique, insensible à la casse/espaces)', function () {
    $user = User::factory()->create();
    // Les codes sont stockés hachés SHA-256 (SEC-M3).
    $user->updateQuietly([
        'two_factor_recovery_codes' => [
            hash('sha256', 'ABCD-1234'),
            hash('sha256', 'WXYZ-9876'),
        ],
    ]);

    // Code valide soumis en minuscules + espaces → normalisé, accepté et consommé.
    expect($user->useRecoveryCode('  abcd-1234  '))->toBeTrue();

    // Usage unique : le même code ne repasse pas.
    expect($user->fresh()->useRecoveryCode('ABCD-1234'))->toBeFalse();

    // L'autre code reste utilisable une fois.
    expect($user->fresh()->useRecoveryCode('WXYZ-9876'))->toBeTrue();
    expect($user->fresh()->two_factor_recovery_codes ?? [])->toBeEmpty();
});

it('useRecoveryCode() rejette un code inconnu', function () {
    $user = User::factory()->create();
    $user->updateQuietly(['two_factor_recovery_codes' => [hash('sha256', 'ABCD-1234')]]);

    expect($user->useRecoveryCode('0000-0000'))->toBeFalse();
    // Aucun code n'a été consommé.
    expect($user->fresh()->two_factor_recovery_codes)->toHaveCount(1);
});

// ── Garde mass-assignment (SEC-C1/C2) ─────────────────────────────────────────

it('interdit le mass-assignment des champs sensibles', function () {
    $user = User::create([
        'name'                       => 'Test',
        'email'                      => 'guard@example.test',
        'password'                   => 'password',
        // Tentatives d'injection : doivent être ignorées (hors $fillable).
        'two_factor_secret'          => 'HACKED',
        'two_factor_recovery_codes'  => ['pwned'],
        'last_login_ip'              => '6.6.6.6',
    ]);

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull()
        ->and($user->last_login_ip)->toBeNull();
});

it('masque les champs sensibles dans la sérialisation', function () {
    $user  = User::factory()->create();
    $user->updateQuietly(['two_factor_secret' => 'JBSWY3DPEHPK3PXP']);
    $array = $user->fresh()->toArray();

    expect($array)->not->toHaveKey('password')
        ->and($array)->not->toHaveKey('two_factor_secret')
        ->and($array)->not->toHaveKey('two_factor_recovery_codes')
        ->and($array)->not->toHaveKey('last_login_ip');
});

// ── Cloisonnement multi-tenant : professionalAccountIds() ─────────────────────

it('professionalAccountIds() renvoie un tableau vide sans compte pro', function () {
    expect(User::factory()->create()->professionalAccountIds())->toBe([]);
});

it('professionalAccountIds() renvoie les IDs (entiers) des comptes rattachés', function () {
    $user     = User::factory()->create();
    $accountA = ProfessionalAccount::create(['owner_user_id' => $user->id, 'company_name' => 'Cabinet A']);
    $accountB = ProfessionalAccount::create(['owner_user_id' => $user->id, 'company_name' => 'Cabinet B']);
    $other    = ProfessionalAccount::create(['owner_user_id' => User::factory()->create()->id, 'company_name' => 'Autre']);

    $user->professionalAccounts()->attach($accountA->id, ['role' => 'owner']);
    $user->professionalAccounts()->attach($accountB->id, ['role' => 'member']);

    $ids = $user->professionalAccountIds();

    expect($ids)->toEqualCanonicalizing([$accountA->id, $accountB->id])
        ->and($ids)->not->toContain($other->id)
        ->and($ids)->each->toBeInt();
});

// ── XP : totalXp() ────────────────────────────────────────────────────────────

it('totalXp() somme les xp_total de toutes les lignes de progression', function () {
    $user = User::factory()->create();
    // test_id laissé à null (aucune ligne `tests` requise, pas de FK à satisfaire ;
    // SQLite comme MySQL autorisent plusieurs NULL dans un index unique).
    GamificationProgress::create(['user_id' => $user->id, 'test_id' => null, 'xp_total' => 150, 'level' => 1]);
    GamificationProgress::create(['user_id' => $user->id, 'test_id' => null, 'xp_total' => 250, 'level' => 2]);
    // XP d'un autre utilisateur : ne doit pas compter.
    GamificationProgress::create(['user_id' => User::factory()->create()->id, 'test_id' => null, 'xp_total' => 999, 'level' => 1]);

    expect($user->totalXp())->toBe(400);
});

it('totalXp() vaut 0 sans progression', function () {
    expect(User::factory()->create()->totalXp())->toBe(0);
});

// ── Grimoire : getOrCreateGrimoire() ──────────────────────────────────────────

it('getOrCreateGrimoire() crée puis renvoie le même grimoire (idempotent)', function () {
    $user = User::factory()->create();

    $first  = $user->getOrCreateGrimoire();
    $second = $user->getOrCreateGrimoire();

    expect($first)->toBeInstanceOf(ProfileGrimoire::class)
        ->and($first->user_id)->toBe($user->id)
        ->and($second->id)->toBe($first->id)
        ->and(ProfileGrimoire::where('user_id', $user->id)->count())->toBe(1);
});
