<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Admins et professionnels : tableau de bord d'administration.
            if ($user->hasRole('admin') || $user->hasRole('professional')) {
                return redirect()->route('admin.dashboard');
            }

            // Candidats : liste des tests si profil complet, sinon onboarding.
            return $user->profile?->isComplete()
                ? redirect()->route('tests.index')
                : redirect()->route('onboarding.show');
        }

        return Inertia::render('Public/Landing');
    }
}
