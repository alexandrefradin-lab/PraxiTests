<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    // ─── Interface conseiller ─────────────────────────────────────────────────

    /**
     * Formulaire d'invitation individuelle d'un candidat.
     * Accessible aux administrateurs et aux professionnels (rôle 'professional').
     */
    public function create()
    {
        $tests = Test::where('published', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->toArray();

        return Inertia::render('Admin/Invitations/Create', [
            'tests' => $tests,
        ]);
    }

    /**
     * Crée une invitation et envoie l'email (via le hook created du modèle).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'test_ids'   => ['required', 'array', 'min:1'],
            'test_ids.*' => ['integer', 'exists:tests,id'],
            'email'      => ['required', 'email', 'max:180'],
            'first_name' => ['nullable', 'string', 'max:80'],
            'last_name'  => ['nullable', 'string', 'max:80'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ], [
            'test_ids.required' => 'Cochez au moins une épreuve à faire passer.',
            'test_ids.min'      => 'Cochez au moins une épreuve à faire passer.',
        ]);

        $testIds = array_values(array_unique(array_map('intval', $data['test_ids'])));

        // Cloisonnement multi-tenant : on rattache l'invitation au premier compte
        // professionnel de l'utilisateur (ou null si admin sans compte PA).
        $user = auth()->user();
        $professionalAccountId = $user->hasRole('admin')
            ? null
            : $user->professionalAccounts()->value('professional_accounts.id');

        // UNE invitation (un seul email, un seul lien) pour l'ensemble des
        // épreuves cochées. test_id = première épreuve (compat schéma et suivi
        // existant) ; la liste complète vit dans metadata.test_ids et alimente
        // l'email d'invitation.
        TestInvitation::create([
            'test_id'                 => $testIds[0],
            'professional_account_id' => $professionalAccountId,
            'email'                   => $data['email'],
            'first_name'              => $data['first_name'] ?? null,
            'last_name'               => $data['last_name'] ?? null,
            // Message d'invitation : identique pour tous, porté par le template
            // de l'email (plus de message personnalisé par invitation).
            'metadata'                => ['test_ids' => $testIds],
            'expires_at'              => $data['expires_at'] ?? null,
            // token et expires_at par défaut gérés par le hook creating()
        ]);

        $count = count($testIds);

        return redirect()
            ->route('admin.conseiller')
            ->with('success', "Invitation envoyée à {$data['email']} ({$count} épreuve" . ($count > 1 ? 's' : '') . ").");
    }

    // ─── Lien public (atterrissage candidat) ──────────────────────────────────

    public function land(Request $request, string $token)
    {
        $invitation = TestInvitation::where('token', $token)->firstOrFail();

        if (in_array($invitation->status, ['completed', 'expired']) ||
            ($invitation->expires_at && $invitation->expires_at->isPast())) {
            $message = $invitation->status === 'completed'
                ? 'Ce lien a déjà été utilisé.'
                : 'Ce lien a expiré.';
            abort(410, $message);
        }

        // MIN-12: Si un utilisateur connecté accède à ce lien avec un email différent,
        // il ne doit pas pouvoir s'approprier l'invitation d'un tiers.
        if (auth()->check() && $invitation->email && auth()->user()->email !== $invitation->email) {
            abort(403, "Cette invitation ne vous est pas destinée. Déconnectez-vous d'abord.");
        }

        $invitation->update([
            'opened_at' => $invitation->opened_at ?? now(),
            'status'    => $invitation->status === 'sent' ? 'opened' : $invitation->status,
        ]);

        session(['invitation_token' => $token]);
        return redirect()->route('register', ['email' => $invitation->email]);
    }
}
