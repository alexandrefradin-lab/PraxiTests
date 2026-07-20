<?php

namespace App\Http\Controllers;

use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * GdprController — Droits RGPD des utilisateurs
 *
 * Article 15 : Droit d'accès (export des données personnelles)
 * Article 17 : Droit à l'effacement ("droit à l'oubli")
 * Article 20 : Droit à la portabilité
 */
class GdprController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // Droit d'accès — Art. 15 RGPD
    // ──────────────────────────────────────────────────────────────────────────

    public function show(): Response
    {
        return Inertia::render('Candidate/Gdpr', [
            'user' => auth()->user()->only('id', 'name', 'email', 'created_at'),
        ]);
    }

    /**
     * Export JSON des données personnelles du candidat.
     * Inclut : compte, profil, résultats, tentatives, synthèses IA.
     * Exclut : mots de passe, tokens internes, logs système.
     */
    public function export(Request $request): JsonResponse
    {
        // MIN-13: Limiter les exports RGPD à 3 par heure pour éviter l'extraction massive.
        $rlKey = 'gdpr_export.' . auth()->id();
        if (RateLimiter::tooManyAttempts($rlKey, 3)) {
            $seconds = RateLimiter::availableIn($rlKey);
            abort(429, "Trop de demandes d'export. Réessayez dans {$seconds} secondes.");
        }
        RateLimiter::hit($rlKey, 3600); // fenêtre de 1 heure

        $user = $request->user()->load([
            'profile',
            'attempts.test:id,name,slug',
            'attempts.result:id,attempt_id,scoring,ai_synthesis,suggested_jobs,strengths,growth_areas,generated_at,ai_driver,ai_model,ai_generated_at',
            'attempts.answers:id,attempt_id,question_id,value,created_at',
            'profileGrimoire',
        ]);

        $export = [
            'export_date'  => now()->toIso8601String(),
            'export_scope' => 'Données personnelles PraxiQuest — Art. 15 RGPD',
            'account'      => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'profile'      => $user->profile ? [
                'status'            => $user->profile->status,
                'status_since'      => $user->profile->status_since?->toDateString(),
                'current_role'      => $user->profile->current_role,
                'industry'          => $user->profile->industry,
                'cv_original_name'  => $user->profile->cv_original_name,
                'consent_data'      => $user->profile->consent_data,
                'consent_marketing' => $user->profile->consent_marketing,
                'completed_at'      => $user->profile->completed_at?->toIso8601String(),
            ] : null,
            'test_results' => $user->attempts->map(fn (TestAttempt $attempt) => [
                'test'          => $attempt->test?->name,
                'status'        => $attempt->status,
                'started_at'    => $attempt->started_at?->toIso8601String(),
                'completed_at'  => $attempt->completed_at?->toIso8601String(),
                'scoring'       => $attempt->result?->scoring,
                'ai_synthesis'  => $attempt->result?->ai_synthesis,
                'suggested_jobs' => $attempt->result?->suggested_jobs,
                'ai_disclaimer' => $attempt->result?->aiDisclaimer(),
            ])->values(),
            'grimoire'     => $user->profileGrimoire ? [
                'synthesis'     => $user->profileGrimoire->synthesis,
                'voies'         => $user->profileGrimoire->voies,
                'generated_at'  => $user->profileGrimoire->generated_at?->toIso8601String(),
                'ai_disclaimer' => $user->profileGrimoire->aiDisclaimer(),
            ] : null,
        ];

        return response()->json($export, 200, [
            'Content-Disposition' => 'attachment; filename="praxiquest-mes-donnees-' . now()->format('Ymd') . '.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Droit à l'effacement — Art. 17 RGPD
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Suppression complète et irréversible du compte.
     * Requiert confirmation du mot de passe (protection contre CSRF / erreur).
     *
     * Données supprimées :
     *  - Fichier CV (Storage local)
     *  - Profil (données psycho, consentements)
     *  - Tentatives, réponses, résultats (données psychométriques)
     *  - Compte utilisateur
     *  - Abonnement Stripe annulé avant suppression
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        DB::transaction(function () use ($user) {
            // 1. Annuler l'abonnement Stripe si actif
            $this->cancelSubscription($user);

            // 2. Supprimer le CV du stockage
            $this->deleteCvFile($user);

            // 3. Purger les données psychométriques : test_attempts.user_id est
            //    en nullOnDelete (passations anonymes/360), donc sans cette purge
            //    explicite les tentatives — et par cascade DB les réponses et
            //    résultats — survivraient anonymisées au lieu d'être effacées.
            $user->attempts()->forceDelete();

            // 4. Supprimer le compte (le profil suit par cascade DB)
            $user->forceDelete();
        });

        // Déconnecter la session
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Votre compte et toutes vos données ont été supprimés définitivement.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Suppression du CV seul — Art. 17 RGPD (granularité)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Supprime uniquement le fichier CV sans supprimer le compte.
     * Le profil reste intact ; l'utilisateur peut re-uploader un CV.
     */
    public function destroyCv(Request $request): RedirectResponse
    {
        $user    = $request->user();
        $profile = $user->profile;

        abort_unless($profile && $profile->cv_path, 404, 'Aucun CV à supprimer.');

        $this->deleteCvFile($user);

        $profile->update([
            'cv_path'           => null,
            'cv_original_name'  => null,
            'cv_extracted_text' => null,
        ]);

        return back()->with('success', 'Votre CV a été supprimé définitivement.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privés
    // ──────────────────────────────────────────────────────────────────────────

    protected function cancelSubscription(User $user): void
    {
        try {
            if (method_exists($user, 'subscribed') && $user->subscribed()) {
                $user->subscription()->cancelNow();
            }
        } catch (\Throwable $e) {
            logger()->warning("GDPR: Stripe subscription cancel failed for user #{$user->id}: {$e->getMessage()}");
            // Ne pas bloquer la suppression si Stripe échoue
        }
    }

    protected function deleteCvFile(User $user): void
    {
        $profile = $user->profile;
        if ($profile && $profile->cv_path) {
            try {
                Storage::disk('local')->delete($profile->cv_path);
            } catch (\Throwable $e) {
                logger()->warning("GDPR: CV file deletion failed for user #{$user->id}: {$e->getMessage()}");
            }
        }
    }
}
