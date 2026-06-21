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

        return Inertia::render('Praxis360/Panel', [
            'attempt'    => $attempt->only('id'),
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

        foreach ($pending as $invitation) {
            Mail::to($invitation->email)->queue(new EvaluatorInvitationMail($invitation, $candidate));
            $invitation->update(['status' => 'sent', 'sent_at' => now()]);
        }

        return back()->with('success', "{$pending->count()} invitation(s) envoyée(s).");
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
