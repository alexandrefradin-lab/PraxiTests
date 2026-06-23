<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ajoute des en-têtes de sécurité HTTP sur les réponses (cf. audit F-7).
 *
 * On reste volontairement prudent sur la CSP : l'app utilise Vite + Inertia
 * (scripts inline pour le payload de page), donc une CSP stricte casserait le
 * rendu. On pose donc les en-têtes sans risque et une CSP minimale en
 * Report-Only, à durcir progressivement une fois les sources auditées.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'SAMEORIGIN',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'X-XSS-Protection'        => '0',
            'Permissions-Policy'      => 'geolocation=(), microphone=(), camera=()',
        ];

        // HSTS uniquement en HTTPS (évite de casser le local en http).
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
