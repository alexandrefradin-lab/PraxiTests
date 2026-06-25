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
}
