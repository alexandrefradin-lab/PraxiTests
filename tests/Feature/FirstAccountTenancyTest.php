<?php

/**
 * Rattachement tenant des écritures (phase 3 — refactor god-model).
 *
 * Fige le comportement AVANT d'unifier les deux lectures « premier compte
 * professionnel » (InvitationController via ->value(), CampaignController via
 * professionalAccountIds()[0]) sur une source unique du concern
 * BelongsToProfessionalAccounts. Un swap qui changerait le compte rattaché
 * (mauvais cabinet, admin non null, pro sans compte) vire immédiatement au rouge.
 */

use App\Models\EmailCampaign;
use App\Models\ProfessionalAccount;
use App\Models\Test;
use App\Models\TestInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

function fatMakeTest(): Test
{
    return Test::create([
        'slug'              => 'fat-test-' . uniqid(),
        'name'              => 'First Account Test',
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => true,
    ]);
}

function fatMakeUser(string $role): User
{
    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);
    return $user;
}

function fatMakeAccountFor(User $user, string $name): ProfessionalAccount
{
    $account = ProfessionalAccount::create(['owner_user_id' => $user->id, 'company_name' => $name]);
    $user->professionalAccounts()->attach($account->id, ['role' => 'owner']);
    return $account;
}

function fatCampaignPayload(): array
{
    return [
        'name'      => 'Campagne test',
        'subject'   => 'Objet test',
        'body_html' => '<p>Bonjour</p>',
    ];
}

// ─── Invitations (InvitationController::store) ────────────────────────────────

it('rattache l invitation au premier compte professionnel de son créateur', function () {
    Mail::fake();
    $pro = fatMakeUser('professional');
    $first  = fatMakeAccountFor($pro, 'Cabinet Premier');
    $second = fatMakeAccountFor($pro, 'Cabinet Second');

    $this->actingAs($pro)
        ->post(route('admin.invitations.store'), [
            'test_ids' => [fatMakeTest()->id],
            'email'    => 'candidat@example.test',
        ])
        ->assertSessionHasNoErrors();

    expect(TestInvitation::sole()->professional_account_id)->toBe($first->id)
        ->and($first->id)->not->toBe($second->id);
});

it('rattache l invitation d un admin à aucun compte (null)', function () {
    Mail::fake();
    $admin = fatMakeUser('admin');
    // Même détenteur d'un compte, l'admin invite hors cloisonnement.
    fatMakeAccountFor($admin, 'Cabinet Admin');

    $this->actingAs($admin)
        ->post(route('admin.invitations.store'), [
            'test_ids' => [fatMakeTest()->id],
            'email'    => 'candidat@example.test',
        ])
        ->assertSessionHasNoErrors();

    expect(TestInvitation::sole()->professional_account_id)->toBeNull();
});

it('crée l invitation d un professionnel sans compte avec un rattachement null', function () {
    Mail::fake();
    $pro = fatMakeUser('professional');

    $this->actingAs($pro)
        ->post(route('admin.invitations.store'), [
            'test_ids' => [fatMakeTest()->id],
            'email'    => 'candidat@example.test',
        ])
        ->assertSessionHasNoErrors();

    expect(TestInvitation::sole()->professional_account_id)->toBeNull();
});

// ─── Campagnes (CampaignController::store) ────────────────────────────────────

it('rattache la campagne au premier compte professionnel de son créateur', function () {
    $pro = fatMakeUser('professional');
    $first = fatMakeAccountFor($pro, 'Cabinet Premier');
    fatMakeAccountFor($pro, 'Cabinet Second');

    $this->actingAs($pro)
        ->post(route('admin.campaigns.store'), fatCampaignPayload())
        ->assertSessionHasNoErrors();

    expect(EmailCampaign::sole()->professional_account_id)->toBe($first->id);
});

it('rattache la campagne d un admin à aucun compte (null)', function () {
    $admin = fatMakeUser('admin');
    fatMakeAccountFor($admin, 'Cabinet Admin');

    $this->actingAs($admin)
        ->post(route('admin.campaigns.store'), fatCampaignPayload())
        ->assertSessionHasNoErrors();

    expect(EmailCampaign::sole()->professional_account_id)->toBeNull();
});

it('crée la campagne d un professionnel sans compte avec un rattachement null', function () {
    $pro = fatMakeUser('professional');

    $this->actingAs($pro)
        ->post(route('admin.campaigns.store'), fatCampaignPayload())
        ->assertSessionHasNoErrors();

    expect(EmailCampaign::sole()->professional_account_id)->toBeNull();
});
