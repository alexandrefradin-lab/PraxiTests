<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /** Clés IA qui doivent être chiffrées en base. */
    private const ENCRYPTED_KEYS = [
        'anthropic_api_key',
        'openai_api_key',
        'mistral_api_key',
    ];

    public function show()
    {
        $raw = Setting::getGroup('ai');

        // Masquer les clés existantes pour l'affichage (on n'envoie jamais la valeur en clair)
        $masked = [];
        foreach ($raw as $key => $value) {
            $masked[$key] = (in_array($key, self::ENCRYPTED_KEYS, true) && $value)
                ? '••••••••'   // juste indiquer qu'une clé est enregistrée
                : $value;
        }

        return Inertia::render('Admin/Settings', [
            'settings' => $masked,
            'drivers'  => array_keys(config('ai.drivers')),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'default_driver'      => ['required', 'string', 'in:anthropic,openai,mistral,ollama'],
            'anthropic_api_key'   => ['nullable', 'string'],
            'anthropic_model'     => ['nullable', 'string', 'max:120'],
            'openai_api_key'      => ['nullable', 'string'],
            'openai_model'        => ['nullable', 'string', 'max:120'],
            'mistral_api_key'     => ['nullable', 'string'],
            'mistral_model'       => ['nullable', 'string', 'max:120'],
            'ollama_base_url'     => ['nullable', 'url', 'max:255'],
            'ollama_model'        => ['nullable', 'string', 'max:120'],
        ]);

        foreach ($data as $key => $value) {
            // Ne pas écraser une clé chiffrée si l'user n'a rien saisi
            // (l'affichage renvoie '••••••••' mais on l'ignore)
            if (in_array($key, self::ENCRYPTED_KEYS, true)) {
                if (blank($value) || $value === '••••••••') continue;
                Setting::set('ai', $key, $value, true);
            } else {
                if ($value !== null) {
                    Setting::set('ai', $key, $value, false);
                }
            }
        }

        return back()->with('success', 'Configuration IA enregistrée.');
    }
}
