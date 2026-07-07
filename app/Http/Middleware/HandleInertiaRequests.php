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
                'user' => $request->user()?->only(['id', 'name', 'email', 'locale', 'ui_theme']),
                // Permet d'afficher un bandeau « confirmez votre email » côté front.
                'email_verified' => (bool) $request->user()?->hasVerifiedEmail(),
                // Navigation admin : masquer les entrées réservées (Tests, Plugins,
                // Réglages…) aux professionnels. Purement cosmétique — la sécurité
                // reste portée par le middleware role: des routes.
                'is_admin' => (bool) $request->user()?->hasRole('admin'),
            ],
            // Total d'Éclats global (lazy) — alimente la barre du layout candidat
            // sur toutes les pages, pas seulement pendant une tentative.
            // Mis en cache 60 s pour éviter une requête SQL à chaque page (ARC-m2).
            // Le cache est invalidé dans GamificationEngine::awardXp() après chaque gain d'Éclats.
            'gamification' => fn () => $request->user()
                ? \Illuminate\Support\Facades\Cache::remember(
                    "eclats.{$request->user()->id}",
                    60,
                    function () use ($request) {
                        $user = $request->user();
                        $data = app(\Praxis\Core\Gamification\GamificationEngine::class)
                            ->globalProgressOf($user);
                        // Nombre de trésors débloqués (comparaison par seuil, sans matching profil)
                        // Utilisé par le Layout pour afficher un badge "nouveau trésor" sur le menu.
                        try {
                            $data['treasure_unlocked_count'] = app(\Praxis\Core\Gamification\RewardCatalog::class)
                                ->all()
                                ->filter(fn ($r) => ($data['xp_total'] ?? 0) >= ($r['threshold'] ?? PHP_INT_MAX))
                                ->count();
                        } catch (\Throwable $e) {
                            $data['treasure_unlocked_count'] = 0;
                        }
                        return $data;
                    }
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
