<?php

/**
 * Cloisonnement multi-tenant des dashboards (phase 2 — refactor god-model).
 *
 * Fige le comportement AVANT de remplacer les requêtes pivot recodées à la main
 * dans DashboardController et ConseillerDashboardController par la source unique
 * User::professionalAccountIds(). Un swap qui changerait le périmètre visible
 * (leads d'un autre cabinet, invitations d'un autre compte, edge case « pro sans
 * compte ») vire immédiatement au rouge.
 */

use App\Models\Lead;
use App\Models\ProfessionalAccount;
use App\Models\Test;
use App\Models\TestInvitation;
use App\Models\User;
use Spatie\Permission\Models\Role;

function dashMakeTest(): Test
{
    return Test::create([
        'slug'              => 'dash-test-' . uniqid(),
        'name'              => 'Dashboard Test',
        'type'              => 'questionnaire',
        'scoring_engine'    => 'default',
        'estimated_minutes' => 5,
        'published'         => true,
    ]);
}

function dashMakeProfessional(): User
{
    Role::firstOrCreate(['name' => 'professional', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('professional');
    return $user;
}

function dashMakeAccountFor(User $user, string $name): ProfessionalAccount
{
    $account = ProfessionalAccount::create(['owner_user_id' => $user->id, 'company_name' => $name]);
    $user->professionalAccounts()->attach($account->id, ['role' => 'owner']);
    return $account;
}

// ─── Dashboard admin (DashboardController) ────────────────────────────────────

it('restreint le dashboard aux leads des comptes du professionnel', function () {
    $proA = dashMakeProfessional();
    $proB = dashMakeProfessional();

    $accountA = dashMakeAccountFor($proA, 'Cabinet A');
    $accountB = dashMakeAccountFor($proB, 'Cabinet B');

    $leadOfA = Lead::create([
        'professional_account_id' => $accountA->id,
        'email'                   => 'lead-a@example.test',
        'status'                  => 'new',
    ]);
    $leadOfB = Lead::create([
        'professional_account_id' => $accountB->id,
        'email'                   => 'lead-b@example.test',
        'status'                  => 'new',
    ]);

    $this->actingAs($proA)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.leads_new', 1)
            ->where('recent_leads', fn ($leads) =>
                collect($leads)->pluck('id')->contains($leadOfA->id)
                && ! collect($leads)->pluck('id')->contains($leadOfB->id)
            )
        );
});

it('rend le dashboard sans erreur pour un professionnel sans compte rattaché', function () {
    // Edge case « ?: [0] » : accountIds vide ne doit ni crasher ni tout montrer.
    $pro = dashMakeProfessional();

    Lead::create([
        'professional_account_id' => dashMakeAccountFor(dashMakeProfessional(), 'Cabinet X')->id,
        'email'                   => 'lead-x@example.test',
        'status'                  => 'new',
    ]);

    $this->actingAs($pro)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.leads_new', 0)
            ->where('recent_leads', fn ($leads) => collect($leads)->isEmpty())
        );
});

// ─── Dashboard conseiller (ConseillerDashboardController) ────────────────────

it('restreint le funnel conseiller aux invitations des comptes du professionnel', function () {
    $proA = dashMakeProfessional();
    $proB = dashMakeProfessional();

    $accountA = dashMakeAccountFor($proA, 'Cabinet A');
    $accountB = dashMakeAccountFor($proB, 'Cabinet B');

    $test = dashMakeTest();
    TestInvitation::create([
        'test_id'                 => $test->id,
        'professional_account_id' => $accountA->id,
        'email'                   => 'invite-a@example.test',
        'status'                  => 'pending',
    ]);
    TestInvitation::create([
        'test_id'                 => $test->id,
        'professional_account_id' => $accountB->id,
        'email'                   => 'invite-b@example.test',
        'status'                  => 'pending',
    ]);

    $this->actingAs($proA)
        ->get(route('admin.conseiller'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            // funnel[0] = « Invités » : seule l'invitation du cabinet A compte.
            ->where('funnel.0.value', 1)
        );
});

it('rend le dashboard conseiller sans erreur pour un professionnel sans compte', function () {
    $pro = dashMakeProfessional();

    TestInvitation::create([
        'test_id'                 => dashMakeTest()->id,
        'professional_account_id' => dashMakeAccountFor(dashMakeProfessional(), 'Cabinet Y')->id,
        'email'                   => 'invite-y@example.test',
        'status'                  => 'pending',
    ]);

    $this->actingAs($pro)
        ->get(route('admin.conseiller'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('funnel.0.value', 0));
});
