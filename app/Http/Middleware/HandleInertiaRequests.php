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
            ],
            // Total d'Éclats global (lazy) — alimente la barre du layout candidat
            // sur toutes les pages, pas seulement pendant une tentative.
            'gamification' => fn () => $request->user()
                ? app(\Praxis\Core\Gamification\GamificationEngine::class)->globalProgressOf($request->user())
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
                'achievement' => fn () => $request->session()->get('achievement'),
            ],
        ]);
    }
}
