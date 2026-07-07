<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UiThemeController extends Controller
{
    /**
     * Bascule le parcours visuel de l'utilisateur.
     * 'medieval' = univers Parchemin/Or · 'corporate' = cabinet de conseil.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ui_theme' => ['required', 'in:medieval,corporate'],
        ]);

        $request->user()->update(['ui_theme' => $data['ui_theme']]);

        return back();
    }
}
