<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirection 301 vers le domaine canonique (www.praxiquest.fr).
 *
 * Le même docroot OVH est servi par praxiquest.decisionpro.fr et
 * www.praxiquest.fr : sans canonique, le SEO se divise et les cookies de
 * session ne se partagent pas entre les deux hôtes. On redirige tout GET/HEAD
 * vers l'hôte canonique en préservant chemin et query string.
 *
 * Garde-fous :
 * - inactif tant que CANONICAL_REDIRECT_ENABLED n'est pas true (défaut false) ;
 * - jamais en local/testing (APP_ENV) ;
 * - seuls GET/HEAD sont redirigés — rediriger un POST (webhook Stripe, formulaires
 *   soumis depuis un vieux lien) perdrait le corps de la requête.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonical = (string) config('praxiquest.canonical.host', '');

        if (
            ! config('praxiquest.canonical.enabled')
            || $canonical === ''
            || ! app()->environment('production')
            || ! $request->isMethod('GET') && ! $request->isMethod('HEAD')
            || strcasecmp($request->getHost(), $canonical) === 0
        ) {
            return $next($request);
        }

        $url = $request->getScheme() . '://' . $canonical . $request->getRequestUri();

        return redirect()->away($url, 301);
    }
}
