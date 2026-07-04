<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StreamsCsv;
use App\Models\AuditLog;
use App\Models\Test;
use App\Models\TestInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvitationController extends Controller
{
    use StreamsCsv;

    // ─── Interface conseiller ─────────────────────────────────────────────────

    /**
     * Liste des invitations envoyées : suivi (en attente / ouverte / terminée /
     * expirée), relance et corbeille. Cloisonnement multi-tenant identique aux
     * leads : un professionnel ne voit que les invitations de ses comptes.
     */
    public function index(Request $request)
    {
        $q = $this->filteredQuery($request)->with('test:id,name')->latest();

        $invitations = $q->paginate(30)->withQueryString()
            ->through(fn (TestInvitation $inv) => [
                'id'         => $inv->id,
                'email'      => $inv->email,
                'name'       => trim(($inv->first_name ?? '') . ' ' . ($inv->last_name ?? '')) ?: null,
                'test'       => $inv->test?->name,
                'tests_count'=> is_array($inv->metadata['test_ids'] ?? null) ? count($inv->metadata['test_ids']) : 1,
                'status'     => $inv->status,
                'is_expired' => $inv->isExpired() && $inv->status !== 'completed',
                'sent_at'    => $inv->sent_at?->format('d/m/Y H:i'),
                'opened_at'  => $inv->opened_at?->format('d/m/Y H:i'),
                'expires_at' => $inv->expires_at?->format('d/m/Y'),
                'consent'    => (bool) $inv->consent_share_professional,
                'deleted_at' => $inv->deleted_at?->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Admin/Invitations/Index', [
            'invitations' => $invitations,
            'filters'     => $request->only(['status', 'search', 'trashed']),
        ]);
    }

    /**
     * Relance une invitation : renvoie l'email au candidat. Si le lien est
     * expiré, l'expiration est repoussée de 30 jours pour que le lien renvoyé
     * soit utilisable.
     */
    public function resend(TestInvitation $invitation)
    {
        $this->authorize('resend', $invitation);

        abort_if($invitation->status === 'completed', 422, 'Ce candidat a déjà terminé ses épreuves.');

        if ($invitation->isExpired()) {
            $invitation->expires_at = now()->addDays(30);
        }
        if ($invitation->status === 'expired') {
            $invitation->status = 'sent';
        }

        try {
            \Illuminate\Support\Facades\Mail::to($invitation->email)
                ->queue(new \App\Mail\CandidateInvitationMail($invitation));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', "L'email n'a pas pu être envoyé (SMTP). Réessayez plus tard.");
        }

        $invitation->sent_at = now();
        $invitation->save();

        AuditLog::record('invitation.resent', $invitation, ['email' => $invitation->email]);

        return back()->with('success', "Invitation relancée pour {$invitation->email}.");
    }

    /** Place une invitation dans la corbeille (le lien devient inutilisable). */
    public function destroy(TestInvitation $invitation)
    {
        $this->authorize('delete', $invitation);
        AuditLog::record('invitation.destroyed', $invitation, ['email' => $invitation->email]);
        $invitation->delete();
        return back()->with('success', 'Invitation placée dans la corbeille.');
    }

    /** Restaure une invitation depuis la corbeille. */
    public function restore(int $id)
    {
        $invitation = TestInvitation::withTrashed()->findOrFail($id);
        $this->authorize('restore', $invitation);
        $invitation->restore();
        AuditLog::record('invitation.restored', $invitation, ['email' => $invitation->email]);
        return back()->with('success', 'Invitation restaurée.');
    }

    /** Export CSV (mêmes filtres et cloisonnement que la liste). */
    public function export(Request $request): StreamedResponse
    {
        $q = $this->filteredQuery($request)->with('test:id,name')->latest();
        AuditLog::record('invitation.exported', null, $request->only(['status', 'search', 'trashed']));

        return $this->streamCsv('invitations-' . now()->format('Y-m-d') . '.csv', [
            'Email', 'Prénom', 'Nom', 'Épreuve', 'Statut', 'Envoyée le', 'Ouverte le', 'Expire le', 'Consentement partage',
        ], function () use ($q) {
            foreach ($q->lazy(500) as $inv) {
                yield [
                    $inv->email,
                    $inv->first_name,
                    $inv->last_name,
                    $inv->test?->name,
                    $inv->status,
                    $inv->sent_at?->format('d/m/Y H:i'),
                    $inv->opened_at?->format('d/m/Y H:i'),
                    $inv->expires_at?->format('d/m/Y'),
                    $inv->consent_share_professional ? 'oui' : 'non',
                ];
            }
        });
    }

    /** Requête filtrée (statut, recherche, corbeille) + cloisonnement tenant. */
    protected function filteredQuery(Request $request)
    {
        $q = TestInvitation::query();

        if ($request->boolean('trashed')) {
            $q->onlyTrashed();
        }

        if (!auth()->user()->hasRole('admin')) {
            $q->whereIn('professional_account_id', auth()->user()->professionalAccountIds() ?: [0]);
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'expired') {
                // Statut virtuel : lien périmé sans complétion
                $q->where('expires_at', '<', now())->where('status', '!=', 'completed');
            } elseif (in_array($status, ['pending', 'sent', 'opened', 'started', 'completed'], true)) {
                $q->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(fn ($x) => $x->where('email', 'like', "%{$s}%")
                ->orWhere('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%"));
        }

        return $q;
    }

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
