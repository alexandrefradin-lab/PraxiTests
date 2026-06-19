<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\CvUploadRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Praxis\Core\AI\Services\CvExtractionService;
use Praxis\Core\Plugins\PluginHooks;

class OnboardingController extends Controller
{
    public function show(): Response
    {
        $profile = auth()->user()->profile;

        return Inertia::render('Candidate/Onboarding', [
            'profile'  => $profile,
            'statuses' => config('praxiquest.profile.statuses'),
            'cv_max_size_kb'   => config('praxiquest.profile.cv_max_size_kb'),
            'cv_allowed_mimes' => config('praxiquest.profile.cv_allowed_mimes'),
        ]);
    }

    public function store(Request $request, CvExtractionService $cv): RedirectResponse
    {
        $data = $this->validateOnboarding($request);
        $cvPath = $this->storeCv($request);

        $profile = Profile::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'status'        => $data['status'],
                'status_since'  => $data['status_since'],
                'status_months' => (int) abs(now()->diffInMonths($data['status_since'])),
                'current_role'  => $data['current_role'] ?? null,
                'industry'      => $data['industry'] ?? null,
                'cv_path'       => $cvPath,
                'cv_original_name' => basename($request->file('cv')->getClientOriginalName()),
                'consent_data'  => true,
                'consent_marketing' => $data['consent_marketing'] ?? false,
                'completed_at'  => now(),
            ]
        );

        \App\Jobs\ExtractCvDataJob::dispatch($profile->id);
        PluginHooks::doAction('profile.completed', $profile);

        return redirect()->route('tests.index')->with('success', 'Profil enregistré.');
    }

    public function edit(): Response
    {
        $profile = auth()->user()->profile;

        return Inertia::render('Candidate/Onboarding', [
            'profile'  => $profile,
            'statuses' => config('praxiquest.profile.statuses'),
            'cv_max_size_kb'   => config('praxiquest.profile.cv_max_size_kb'),
            'cv_allowed_mimes' => config('praxiquest.profile.cv_allowed_mimes'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status'       => ['required', 'in:' . implode(',', array_keys(config('praxiquest.profile.statuses')))],
            'status_since' => ['required', 'date', 'before_or_equal:today'],
            'current_role' => ['nullable', 'string', 'max:120'],
            'industry'     => ['nullable', 'string', 'max:120'],
            'consent_marketing' => ['nullable', 'boolean'],
        ]);

        $profile = $request->user()->profile;
        $updateData = [
            'status'        => $data['status'],
            'status_since'  => $data['status_since'],
            'status_months' => now()->diffInMonths($data['status_since']),
            'current_role'  => $data['current_role'] ?? null,
            'industry'      => $data['industry'] ?? null,
            'consent_marketing' => $data['consent_marketing'] ?? $profile->consent_marketing,
        ];

        // Si nouveau CV fourni : valider + remplacer
        if ($request->hasFile('cv')) {
            $cvRequest = CvUploadRequest::createFrom($request);
            $cvRequest->validateResolved();
            $cvRequest->validateMagicBytes();

            $newPath = $this->storeCv($request);
            // Supprimer l'ancien CV
            if ($profile->cv_path) {
                Storage::disk('local')->delete($profile->cv_path);
            }
            $updateData['cv_path']          = $newPath;
            $updateData['cv_original_name'] = basename($request->file('cv')->getClientOriginalName());
            $updateData['cv_extracted_text'] = null;
            $updateData['cv_structured']     = null;

            \App\Jobs\ExtractCvDataJob::dispatch($profile->id);
        }

        $profile->update($updateData);

        return redirect()->route('tests.index')->with('success', 'Profil mis à jour.');
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Valide les champs du formulaire d'onboarding avec le CV obligatoire.
     * SEC : utilise CvUploadRequest pour la double vérification MIME.
     */
    protected function validateOnboarding(Request $request): array
    {
        // 1. Valider les champs texte + CV (extension + MIME navigateur)
        $data = $request->validate([
            'status'        => ['required', 'in:' . implode(',', array_keys(config('praxiquest.profile.statuses')))],
            'status_since'  => ['required', 'date', 'before_or_equal:today'],
            'current_role'  => ['nullable', 'string', 'max:120'],
            'industry'      => ['nullable', 'string', 'max:120'],
            'cv'            => [
                'required',
                'file',
                'mimes:' . implode(',', config('praxiquest.profile.cv_allowed_mimes')),
                'max:' . config('praxiquest.profile.cv_max_size_kb'),
            ],
            'consent_data'  => ['accepted'],
            'consent_marketing' => ['nullable', 'boolean'],
        ]);

        // 2. SEC : vérification magic bytes (résiste au spoofing de MIME)
        $cvReq = CvUploadRequest::createFrom($request);
        $cvReq->validateMagicBytes();

        return $data;
    }

    /**
     * Stocke le fichier CV dans le disque local (hors public web).
     * Le chemin inclut l'ID utilisateur pour l'isolation.
     */
    protected function storeCv(Request $request): string
    {
        return $request->file('cv')->store(
            'cvs/' . $request->user()->id,
            'local'
        );
    }
}
