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
        $isManual = ($data['cv_mode'] ?? 'file') === 'manual';

        $attributes = [
            'status'        => $data['status'],
            'status_since'  => $data['status_since'],
            'status_months' => (int) abs(now()->diffInMonths($data['status_since'])),
            'current_role'  => $data['current_role'] ?? null,
            'industry'      => $data['industry'] ?? null,
            'problematique' => $data['problematique'] ?? null,
            'consent_data'  => true,
            'consent_marketing' => $data['consent_marketing'] ?? false,
            'completed_at'  => now(),
        ];

        if ($isManual) {
            // Saisie manuelle : on alimente directement le Codex sans fichier CV.
            $attributes['cv_path']          = null;
            $attributes['cv_original_name'] = null;
            $attributes['cv_structured']    = [
                'source'    => 'manual',
                'job_title' => $data['cv_job_title'],
                'sector'    => $data['cv_sector'],
                'years'     => $data['cv_years'],
            ];
            // Pré-remplit le poste/secteur du profil si non renseignés en section I.
            $attributes['current_role'] = $attributes['current_role'] ?: $data['cv_job_title'];
            $attributes['industry']     = $attributes['industry'] ?: $data['cv_sector'];
        } else {
            $attributes['cv_path']          = $this->storeCv($request);
            $attributes['cv_original_name'] = basename($request->file('cv')->getClientOriginalName());
        }

        $profile = Profile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $attributes
        );

        // L'extraction IA n'a de sens que si un fichier CV a été déposé.
        // afterResponse : en queue sync (OVH), évite qu'une exception du job
        // (parsing PDF, IA) ne remonte dans la requête HTTP d'onboarding → 500.
        if (! $isManual) {
            \App\Jobs\ExtractCvDataJob::dispatch($profile->id)->afterResponse();
        }
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
        $mode = $request->input('cv_mode') === 'manual' ? 'manual' : 'file';

        $rules = [
            'status'       => ['required', 'in:' . implode(',', array_keys(config('praxiquest.profile.statuses')))],
            'status_since' => ['required', 'date', 'before_or_equal:today'],
            'current_role' => ['nullable', 'string', 'max:120'],
            'industry'     => ['nullable', 'string', 'max:120'],
            'problematique' => ['nullable', 'string', 'max:1000'],
            'consent_marketing' => ['nullable', 'boolean'],
            'cv_mode'      => ['nullable', 'in:file,manual'],
        ];

        // En édition, le CV est facultatif : on ne le remplace que si du nouveau
        // contenu est fourni (fichier OU saisie manuelle). Sinon on conserve l'existant.
        if ($mode === 'manual') {
            $rules['cv_job_title'] = ['required', 'string', 'max:150'];
            $rules['cv_sector']    = ['required', 'string', 'max:150'];
            $rules['cv_years']     = ['required', 'string', 'max:50'];
        } else {
            $rules['cv'] = [
                'nullable',
                'file',
                'mimes:' . implode(',', config('praxiquest.profile.cv_allowed_mimes')),
                'max:' . config('praxiquest.profile.cv_max_size_kb'),
            ];
        }

        $data = $request->validate($rules);

        $profile = $request->user()->profile;
        $updateData = [
            'status'        => $data['status'],
            'status_since'  => $data['status_since'],
            'status_months' => (int) abs(now()->diffInMonths($data['status_since'])),
            'current_role'  => $data['current_role'] ?? null,
            'industry'      => $data['industry'] ?? null,
            'problematique' => $data['problematique'] ?? null,
            'consent_marketing' => $data['consent_marketing'] ?? $profile->consent_marketing,
        ];

        if ($mode === 'manual') {
            // Saisie manuelle des 3 infos : alimente le Codex sans fichier.
            if ($profile->cv_path) {
                Storage::disk('local')->delete($profile->cv_path);
            }
            $updateData['cv_path']          = null;
            $updateData['cv_original_name'] = null;
            $updateData['cv_extracted_text'] = null;
            $updateData['cv_structured']    = [
                'source'    => 'manual',
                'job_title' => $data['cv_job_title'],
                'sector'    => $data['cv_sector'],
                'years'     => $data['cv_years'],
            ];
            $updateData['current_role'] = $updateData['current_role'] ?: $data['cv_job_title'];
            $updateData['industry']     = $updateData['industry'] ?: $data['cv_sector'];
        } elseif ($request->hasFile('cv')) {
            // Nouveau fichier CV : valider + remplacer
            $cvRequest = CvUploadRequest::createFrom($request);
            $cvRequest->validateResolved();
            $cvRequest->validateMagicBytes();

            $newPath = $this->storeCv($request);
            if ($profile->cv_path) {
                Storage::disk('local')->delete($profile->cv_path);
            }
            $updateData['cv_path']          = $newPath;
            $updateData['cv_original_name'] = basename($request->file('cv')->getClientOriginalName());
            $updateData['cv_extracted_text'] = null;
            $updateData['cv_structured']     = null;

            // afterResponse : en queue sync (OVH), évite qu'une exception du job
            // (parsing PDF, IA) ne remonte dans la requête HTTP → 500.
            \App\Jobs\ExtractCvDataJob::dispatch($profile->id)->afterResponse();
        }
        // else : aucun changement de CV → on conserve l'existant.

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
        $mode = $request->input('cv_mode') === 'manual' ? 'manual' : 'file';

        // 1. Champs communs
        $rules = [
            'status'        => ['required', 'in:' . implode(',', array_keys(config('praxiquest.profile.statuses')))],
            'status_since'  => ['required', 'date', 'before_or_equal:today'],
            'current_role'  => ['nullable', 'string', 'max:120'],
            'industry'      => ['nullable', 'string', 'max:120'],
            'problematique' => ['required', 'string', 'max:1000'],
            'consent_data'  => ['accepted'],
            'consent_marketing' => ['nullable', 'boolean'],
            'cv_mode'       => ['nullable', 'in:file,manual'],
        ];

        // 2. Codex de compétences : fichier CV OU saisie manuelle (3 infos)
        if ($mode === 'manual') {
            $rules['cv_job_title'] = ['required', 'string', 'max:150'];
            $rules['cv_sector']    = ['required', 'string', 'max:150'];
            $rules['cv_years']     = ['required', 'string', 'max:50'];
        } else {
            $rules['cv'] = [
                'required',
                'file',
                'mimes:' . implode(',', config('praxiquest.profile.cv_allowed_mimes')),
                'max:' . config('praxiquest.profile.cv_max_size_kb'),
            ];
        }

        $data = $request->validate($rules);
        $data['cv_mode'] = $mode;

        // 3. SEC : vérification magic bytes (résiste au spoofing de MIME) — fichier uniquement
        if ($mode === 'file') {
            $cvReq = CvUploadRequest::createFrom($request);
            $cvReq->validateMagicBytes();
        }

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
