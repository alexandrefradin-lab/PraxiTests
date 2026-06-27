<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email', 'locale']),
                // Permet d'afficher un bandeau « confirmez votre email » côté front.
                'email_verified' => (bool) $request->user()?->hasVerifiedEmail(),
            ],
            // Total d'Éclats global (lazy) — alimente la barre du layout candidat
            // sur toutes les pages, pas seulement pendant une tentative.
            // Mis en cache 60 s pour éviter une requête SQL à chaque page (ARC-m2).
            // Le cache est invalidé dans GamificationEngine::awardXp() après chaque gain d'Éclats.
            'gamification' => fn () => $request->user()
                ? \Illuminate\Support\Facades\Cache::remember(
                    "eclats.{$request->user()->id}",
                    60,
                    fn () => app(\Praxis\Core\Gamification\GamificationEngine::class)
                        ->globalProgressOf($request->user())
                )
                : null,
            'branding' => [
                'name'    => config('praxiquest.branding.name'),
                'tagline' => config('praxiquest.branding.tagline'),
                'logo'    => config('praxiquest.branding.logo'),
                'primary_color'   => config('praxiquest.branding.primary_color'),
                'secondary_color' => config('praxiquest.branding.secondary_color'),
            ],
            'flash' => [
                'success'     => fn () => $request->session()->get('success'),
                'error'       => fn () => $request->session()->get('error'),
                'info'        => fn () => $request->session()->get('info'),
                'warning'     => fn () => $request->session()->get('warning'),
                'status'      => fn () => $request->session()->get('status'),
                'achievement' => fn () => $request->session()->get('achievement'),
            ],
        ]);
    }
}
