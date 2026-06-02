<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
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
        $maxKb = config('praxiquest.profile.cv_max_size_kb');
        $mimes = implode(',', config('praxiquest.profile.cv_allowed_mimes'));

        $data = $request->validate([
            'status'        => ['required', 'in:' . implode(',', array_keys(config('praxiquest.profile.statuses')))],
            'status_since'  => ['required', 'date', 'before_or_equal:today'],
            'current_role'  => ['nullable', 'string', 'max:120'],
            'industry'      => ['nullable', 'string', 'max:120'],
            'cv'            => ["required", "file", "mimes:{$mimes}", "max:{$maxKb}"],
            'consent_data'  => ['accepted'],
            'consent_marketing' => ['nullable', 'boolean'],
        ]);

        $cvPath = $request->file('cv')->store("cvs/{$request->user()->id}", 'local');

        $profile = Profile::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'status'        => $data['status'],
                'status_since'  => $data['status_since'],
                'status_months' => now()->diffInMonths($data['status_since']),
                'current_role'  => $data['current_role'] ?? null,
                'industry'      => $data['industry'] ?? null,
                'cv_path'       => $cvPath,
                'cv_original_name' => basename($request->file('cv')->getClientOriginalName()),
                'consent_data'  => true,
                'consent_marketing' => $data['consent_marketing'] ?? false,
                'completed_at'  => now(),
            ]
        );

        // Extraction asynchrone (queue)
        \App\Jobs\ExtractCvDataJob::dispatch($profile->id);

        PluginHooks::doAction('profile.completed', $profile);

        return redirect()->route('tests.index')->with('success', 'Profil enregistré.');
    }
}
