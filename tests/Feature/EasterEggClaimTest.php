<?php

/**
 * Non-régression sur la route de récompense des easter eggs.
 *
 * Contexte : POST /easter-egg/claim a renvoyé 500 depuis sa mise en ligne.
 * `EasterEggController::class` était écrit en nom court dans routes/web.php,
 * fichier sans namespace ; Laravel 11 n'appliquant plus de préfixe de
 * contrôleur, l'expression se résolvait en la chaîne "EasterEggController",
 * introuvable au dispatch. Le composant Vue avalait l'erreur et affichait une
 * réussite : aucun Éclat, aucun badge, aucune alerte — pendant des mois.
 *
 * Corrigé le 22/07/2026 en qualifiant pleinement le contrôleur. Ces tests
 * échouent contre le code d'avant : le premier renvoyait 500 au lieu de 200.
 */

use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
    Cache::flush();

    // Les badges des easter eggs viennent de BadgeSeeder en prod ; ici on ne
    // crée que celui utilisé par le slug testé.
    Badge::create([
        'slug'      => 'eveille',
        'name'      => 'Éveillé',
        'criteria'  => ['type' => 'easter_egg'],
        'xp_reward' => 0,
    ]);
});

function eggUser(): User
{
    $user = User::factory()->create(['email_verified_at' => now()]);

    return $user;
}

it('récompense un secret découvert et le trace en base', function () {
    $user = eggUser();

    $res = $this->actingAs($user)->postJson(route('easter-egg.claim'), ['slug' => 'konami']);

    $res->assertOk()
        ->assertJson(['success' => true, 'eclats' => 42]);

    $this->assertDatabaseHas('user_easter_eggs', [
        'user_id' => $user->id,
        'slug'    => 'konami',
    ]);

    expect($user->fresh()->badges()->where('slug', 'eveille')->exists())->toBeTrue();
});

it('refuse un second claim du même secret', function () {
    $user = eggUser();

    $this->actingAs($user)->postJson(route('easter-egg.claim'), ['slug' => 'konami'])->assertOk();

    $this->actingAs($user)
        ->postJson(route('easter-egg.claim'), ['slug' => 'konami'])
        ->assertOk()
        ->assertJson(['already_claimed' => true]);

    // Une seule ligne, donc un seul crédit.
    expect(\DB::table('user_easter_eggs')->where('user_id', $user->id)->count())->toBe(1);
});

it('rejette un slug absent du registre serveur', function () {
    $user = eggUser();

    $this->actingAs($user)
        ->postJson(route('easter-egg.claim'), ['slug' => 'slug_qui_nexiste_pas'])
        ->assertStatus(422);

    expect(\DB::table('user_easter_eggs')->where('user_id', $user->id)->count())->toBe(0);
});

it('exige une authentification', function () {
    $this->postJson(route('easter-egg.claim'), ['slug' => 'konami'])
        ->assertStatus(401);
});
