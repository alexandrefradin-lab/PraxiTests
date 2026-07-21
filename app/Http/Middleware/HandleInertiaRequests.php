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
                // L'abonnement ne concerne que les professionnels : les entrées
                // « Mon abonnement » du layout candidat sont masquées aux autres.
                'is_pro' => (bool) ($request->user()?->hasRole('professional') || $request->user()?->hasRole('admin')),
            ],
            // Badge d'incident dans la sidebar admin : nombre de synthèses IA en
            // échec. Caché 60 s (même donnée que l'alerte du dashboard).
            'admin_alerts' => fn () => $request->user()?->hasRole('admin')
                ? [
                    'failed_insights' => \Illuminate\Support\Facades\Cache::remember(
                        'admin.failed_insights_count',
                        60,
                        fn () => \App\Models\TestResult::where('ai_failed', true)->count(),
                    ),
                ]
                : null,
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
                        // Mini-apps réellement ouvertes (déblocage choisi et persisté)
                        // + solde d'Éclats dépensable. Utilisés par le Layout pour le
                        // badge "nouveau trésor" et l'affichage du portefeuille.
                        try {
                            $catalog = app(\Praxis\Core\Gamification\RewardCatalog::class);

                            if ($catalog->choiceEnabled()) {
                                $spent = \App\Models\MiniAppUnlock::spentBy($user->id);
                                $data['eclats_spent']     = $spent;
                                $data['eclats_available'] = max(0, ($data['xp_total'] ?? 0) - $spent);
                                $data['treasure_unlocked_count'] = count(
                                    \App\Models\MiniAppUnlock::slugsFor($user->id)
                                );
                            } else {
                                // Régime historique : déblocage par comparaison de seuil.
                                $data['eclats_spent']     = 0;
                                $data['eclats_available'] = $data['xp_total'] ?? 0;
                                $data['treasure_unlocked_count'] = $catalog->all()
                                    ->filter(fn ($r) => ($data['xp_total'] ?? 0) >= ($r['threshold'] ?? PHP_INT_MAX))
                                    ->count();
                            }
                        } catch (\Throwable $e) {
                            $data['eclats_spent']     = 0;
                            $data['eclats_available'] = $data['xp_total'] ?? 0;
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
