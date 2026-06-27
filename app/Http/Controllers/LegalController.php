<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class LegalController extends Controller
{
    public function cgu()
    {
        return Inertia::render('Public/CGU');
    }

    /**
     * Page Politique de confidentialité (RGPD — Art. 13/14).
     * Route publique (GET /confidentialite, pas d'auth requise).
     */
    public function confidentialite()
    {
        return Inertia::render('Public/Confidentialite');
    }

    /**
     * Mentions légales (obligatoire — art. 6 LCEN).
     * Identité de l'éditeur, du directeur de publication et de l'hébergeur.
     */
    public function mentions()
    {
        return Inertia::render('Public/Mentions', [
            'legal'   => config('praxiquest.legal'),
            'contact' => config('praxiquest.contact'),
        ]);
    }

    /**
     * Page Contact / Support (publique).
     */
    public function contact()
    {
        return Inertia::render('Public/Contact', [
            'contact' => config('praxiquest.contact'),
        ]);
    }
}
