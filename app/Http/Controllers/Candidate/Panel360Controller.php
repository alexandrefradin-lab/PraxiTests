<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Mail\EvaluatorInvitationMail;
use App\Models\EvaluationInvitation;
use App\Models\EvaluationPanel;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Praxis\Plugins\Praxis360\Support\PanelAggregator;

/**
 * Côté CANDIDAT : ouvre un panel 360° à partir de son auto-évaluation Praxis360,
 * invite ses évaluateurs (manager / pairs / collaborateurs) et suit la collecte.
 */
class Panel360Controller extends Controller
{
    /** Slug du test éligible au 360°. */
    private const TEST_SLUG = 'praxis360';

    /** Nombre minimal d'évaluateurs à désigner avant de démarrer le test. */
    private const MIN_EVALUATORS = 3;

    /** Affiche la page de gestion du panel pour une auto-évaluation donnée. */
    public function manage(TestAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        $attempt->loadMissing('test');
        abort_unless(($attempt->test->slug ?? null) === self::TEST_SLUG, 404, 'Ce test ne propose pas de 360°.');

        $panel = EvaluationPanel::firstOrCreate(
            ['user_id' => auth()->id(), 'test_id' => $attempt->test_id, 'self_attempt_id' => $attempt->id],
            ['status' => 'open', 'anonymity_threshold' => 3],
        );

        $panel->load('invitations');

        // « setup » = étape préalable (auto-évaluation pas encore faite) :
        // le candidat DOIT désigner au moins MIN_EVALUATORS avant de répondre.
        // « manage » = gestion classique depuis la page de résultats.
        $mode = $attempt->status === 'completed' ? 'manage' : 'setup';

        return Inertia::render('Praxis360/Panel', [
            'attempt'    => $attempt->only('id'),
            'mode'           => $mode,
            'minEvaluators'  => self::MIN_EVALUATORS,
            'proceedUrl'     => route('panel360.proceed', $panel->id),
            'panel'      => [
                'id'        => $panel->id,
                'status'    => $panel->status,
                'threshold' => $panel->anonymity_threshold,
            ],
            'relations'  => EvaluationInvitation::RELATION_LABELS,
            'invitations' => $panel->invitations->map(fn ($i) => [
                'id'        => $i->id,
                'name'      => $i->name,
                'email'     => $i->email,
                'relation'  => $i->relation,
                'status'    => $i->status,
                'sent_at'   => $i->sent_at,
            ]),
            'aggregate'  => (new PanelAggregator($panel))->build(),
        ]);
    }

    /** Ajoute un ou plusieurs évaluateurs (statut « pending »). */
    public function invite(Request $request, EvaluationPanel $panel)
    {
        abort_unless($panel->user_id === auth()->id(), 403);

        $data = $request->validate([
            'evaluators'              => ['required', 'array', 'min:1'],
            'evaluators.*.name'       => ['nullable', 'string', 'max:120'],
            'evaluators.*.email'      => ['required', 'email', 'max:190'],
            'evaluators.*.relation'   => ['required', 'in:' . implode(',', EvaluationPanel::RELATIONS)],
        ]);

        foreach ($data['evaluators'] as $ev) {
            // Éviter les doublons d'email sur un même panel.
            $exists = $panel->invitations()->where('email', $ev['email'])->exists();
            if ($exists) {
                continue;
            }
            $panel->invitations()->create([
                'relation' => $ev['relation'],
                'name'     => $ev['name'] ?? null,
                'email'    => $ev['email'],
                'status'   => 'pending',
            ]);
        }

        return back()->with('success', 'Évaluateurs ajoutés.');
    }

    /** Envoie les invitations « pending » par email et les passe à « sent ». */
    public function send(EvaluationPanel $panel)
    {
        abort_unless($panel->user_id === auth()->id(), 403);

        $candidate = auth()->user()->name ?? 'Un candidat';
        $pending   = $panel->invitations()->where('status', 'pending')->get();

        $sent = 0;
        $failed = 0;
        foreach ($pending as $invitation) {
            // Ne passer à « sent » QUE si la mise en file a réussi ;
            // sinon l'invitation reste « pending » et sera ré-émise.
            try {
                Mail::to($invitation->email)->queue(new EvaluatorInvitationMail($invitation, $candidate));
                $invitation->update(['status' => 'sent', 'sent_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                logger()->error("Envoi invitation 360 échoué pour {$invitation->email}: {$e->getMessage()}");
            }
        }

        $msg = "{$sent} invitation(s) envoyée(s).";
        if ($failed > 0) {
            $msg .= " {$failed} échec(s) — réessayez plus tard.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Valide l'étape préalable : au moins MIN_EVALUATORS désignés, envoie les
     * invitations encore en attente, puis bascule le candidat sur son
     * auto-évaluation (les regards seront collectés en parallèle).
     */
    public function proceed(EvaluationPanel $panel)
    {
        abort_unless($panel->user_id === auth()->id(), 403);

        // Chargement unique des invitations (BDD-m3) : évite count() + get() séparés.
        $invitations = $panel->invitations()->get();

        if ($invitations->count() < self::MIN_EVALUATORS) {
            return back()->with('error', 'Indiquez au moins ' . self::MIN_EVALUATORS . ' évaluateurs avant de commencer.');
        }

        // Envoyer toutes les invitations encore « pending ».
        $candidate = auth()->user()->name ?? 'Un candidat';
        foreach ($invitations->where('status', 'pending') as $invitation) {
            Mail::to($invitation->email)->queue(new EvaluatorInvitationMail($invitation, $candidate));
            $invitation->update(['status' => 'sent', 'sent_at' => now()]);
        }

        return redirect()->route('attempt.show', $panel->self_attempt_id);
    }

    /** Retire une invitation tant qu'elle n'a pas été complétée. */
    public function removeInvitation(EvaluationInvitation $invitation)
    {
        abort_unless($invitation->panel->user_id === auth()->id(), 403);
        abort_if($invitation->status === 'completed', 422, 'Cette réponse est déjà enregistrée.');

        $invitation->delete();

        return back()->with('success', 'Invitation retirée.');
    }
}
