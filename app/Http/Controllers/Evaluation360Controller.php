<?php

namespace App\Http\Controllers;

use App\Models\EvaluationInvitation;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Plugins\Praxis360\Data\Questions;

/**
 * Côté ÉVALUATEUR (public, sans compte) : accès par lien tokenisé, réponse au
 * questionnaire 360° formulé à la 3e personne sur le candidat, puis verbatims.
 * Les réponses sont stockées dans un TestAttempt « invité » (user_id null).
 */
class Evaluation360Controller extends Controller
{
    /** Page d'accueil de l'évaluateur : prépare/charge sa tentative et le questionnaire. */
    public function land(string $token)
    {
        $invitation = EvaluationInvitation::where('token', $token)->firstOrFail();
        $panel      = $invitation->panel()->with(['user:id,name', 'test'])->first();

        if ($invitation->isExpired()) {
            return Inertia::render('Evaluation/Closed', ['reason' => 'expired']);
        }
        if ($invitation->isCompleted()) {
            return Inertia::render('Evaluation/Thanks', ['already' => true]);
        }

        // Marquer comme ouverte (1re visite).
        if ($invitation->status === 'sent' || $invitation->status === 'pending') {
            $invitation->update(['status' => 'opened', 'opened_at' => now()]);
        }

        // Tentative « invitée » : réutilisée si déjà entamée.
        $attempt = $invitation->attempt;
        if (!$attempt) {
            $attempt = TestAttempt::create([
                'user_id'          => null,
                'test_id'          => $panel->test_id,
                'panel_id'         => $panel->id,
                'rater_relation'   => $invitation->relation,
                'status'           => 'in_progress',
                'started_at'       => now(),
                'last_activity_at' => now(),
                'progress'         => [],
            ]);
            $invitation->update(['attempt_id' => $attempt->id]);
        }

        return Inertia::render('Evaluation/Rater360', [
            'token'        => $token,
            'subjectName'  => $panel->user->name ?? 'cette personne',
            'relationLabel' => $invitation->relationLabel(),
            'scale'        => Questions::scale(),
            'questions'    => $this->raterQuestions($panel->test),
            'verbatims'    => Questions::verbatims(),
            'answered'     => $attempt->answers()->pluck('value', 'question_id'),
        ]);
    }

    /** Enregistre une réponse (échelle) sur la tentative de l'évaluateur. */
    public function answer(Request $request, string $token)
    {
        $invitation = EvaluationInvitation::where('token', $token)->firstOrFail();
        abort_if($invitation->isExpired() || $invitation->isCompleted(), 403);
        $attempt = $invitation->attempt;
        abort_unless($attempt, 404);

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'value'       => ['required', 'numeric'],
        ]);

        // La question doit appartenir au test du panel.
        $belongs = \App\Models\TestQuestion::where('id', (int) $data['question_id'])
            ->whereHas('section', fn ($q) => $q->where('test_id', $attempt->test_id))
            ->exists();
        abort_unless($belongs, 422, 'Question invalide.');

        $attempt->answers()->updateOrCreate(
            ['question_id' => (int) $data['question_id']],
            ['value' => $data['value'], 'time_spent_seconds' => 0],
        );
        $attempt->update(['last_activity_at' => now()]);

        return back();
    }

    /** Clôture la réponse de l'évaluateur (verbatims + statut completed). */
    public function complete(Request $request, string $token)
    {
        $invitation = EvaluationInvitation::where('token', $token)->firstOrFail();
        abort_if($invitation->isExpired() || $invitation->isCompleted(), 403);
        $attempt = $invitation->attempt;
        abort_unless($attempt, 404);

        $data = $request->validate([
            'verbatims'           => ['nullable', 'array'],
            'verbatims.strength'  => ['nullable', 'string', 'max:2000'],
            'verbatims.growth'    => ['nullable', 'string', 'max:2000'],
            'verbatims.advice'    => ['nullable', 'string', 'max:2000'],
        ]);

        $attempt->update(['status' => 'completed', 'completed_at' => now()]);
        $invitation->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'verbatims'    => $data['verbatims'] ?? null,
        ]);

        return redirect()->route('eval360.thanks', $token);
    }

    public function thanks(string $token)
    {
        return Inertia::render('Evaluation/Thanks', ['already' => false]);
    }

    /**
     * Construit la liste des questions évaluateur : prompts à la 3e personne
     * (Questions::forRater) mappés aux question_id réels du test, par position.
     * Hypothèse : l'ordre des questions seedées suit l'ordre de Questions::all()
     * (donc de forRater()). Le mapping se fait par index.
     */
    private function raterQuestions(\App\Models\Test $test): array
    {
        $test->load(['sections' => fn ($q) => $q->orderBy('order'),
                     'sections.questions' => fn ($q) => $q->orderBy('order')]);

        $dbQuestions = [];
        foreach ($test->sections as $section) {
            foreach ($section->questions as $q) {
                $dbQuestions[] = $q;
            }
        }

        $rater = Questions::forRater();
        $out   = [];
        foreach ($dbQuestions as $i => $q) {
            $label = $rater[$i]['prompt'] ?? $q->prompt;
            $out[] = [
                'id'      => $q->id,
                'section' => $rater[$i]['section'] ?? null,
                'prompt'  => $label,
            ];
        }
        return $out;
    }
}
