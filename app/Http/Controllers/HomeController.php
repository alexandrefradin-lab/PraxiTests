<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return auth()->user()->profile?->isComplete()
                ? redirect()->route('tests.index')
                : redirect()->route('onboarding.show');
        }

        return Inertia::render('Public/Landing');
    }
}
