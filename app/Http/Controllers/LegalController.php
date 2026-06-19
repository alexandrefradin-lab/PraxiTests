<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class LegalController extends Controller
{
    public function cgu()
    {
        return Inertia::render('Public/CGU');
    }
}
