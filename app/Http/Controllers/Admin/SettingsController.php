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
        'deepseek_api_key',
    ];

    /** Fournisseurs (drivers) sélectionnables par tâche. value = nom du driver. */
    private const PROVIDERS = [
        ['value' => '',                'label' => '(défaut config)'],
        ['value' => 'anthropic',       'label' => 'Claude — Sonnet (qualité)'],
        ['value' => 'anthropic_haiku', 'label' => 'Claude — Haiku (économique)'],
        ['value' => 'openai',          'label' => 'OpenAI'],
        ['value' => 'deepseek',        'label' => 'DeepSeek'],
        ['value' => 'mistral',         'label' => 'Mistral'],
    ];

    /** Tâches IA dont le modèle est pilotable en admin (clé => libellé). */
    private const TASKS = [
        'profile_synthesis'     => 'Synthèse par test',
        'job_suggestions'       => 'Suggestions de métiers (par test)',
        'global_grimoire'       => 'Grimoire — relecture (synthèse)',
        'global_grimoire_voies' => 'Grimoire — pistes métiers',
        'cv_extract'            => 'Extraction du CV',
        'email_personalization' => 'Personnalisation des emails',
        'oracle_chat'           => 'Oracle (chat)',
    ];

    /** Drivers autorisés pour une tâche (sécurité : pas de valeur arbitraire). */
    private function allowedDrivers(): array
    {
        return ['', 'anthropic', 'anthropic_haiku', 'openai', 'deepseek', 'mistral', 'ollama'];
    }

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

        // Réglage courant par tâche (driver + modèle), avec repli sur la config.
        $taskConfig = [];
        foreach (self::TASKS as $task => $label) {
            $taskConfig[$task] = [
                'label'  => $label,
                'driver' => $masked["task_{$task}_driver"] ?? (config("ai.tasks.{$task}.driver") ?? ''),
                'model'  => $masked["task_{$task}_model"]  ?? '',
                'default_model' => config("ai.drivers." . (config("ai.tasks.{$task}.driver") ?: config('ai.default')) . ".model"),
            ];
        }

        return Inertia::render('Admin/Settings', [
            'settings'   => $masked,
            'drivers'    => array_keys(config('ai.drivers')),
            'providers'  => self::PROVIDERS,
            'taskConfig' => $taskConfig,
        ]);
    }

    public function update(Request $request)
    {
        $allowed = $this->allowedDrivers();

        $rules = [
            'default_driver'        => ['required', 'string', 'in:anthropic,openai,mistral,ollama,deepseek'],
            'anthropic_api_key'     => ['nullable', 'string'],
            'anthropic_model'       => ['nullable', 'string', 'max:120'],
            'anthropic_haiku_model' => ['nullable', 'string', 'max:120'],
            'openai_api_key'        => ['nullable', 'string'],
            'openai_model'          => ['nullable', 'string', 'max:120'],
            'deepseek_api_key'      => ['nullable', 'string'],
            'deepseek_model'        => ['nullable', 'string', 'max:120'],
            'deepseek_base_url'     => ['nullable', 'url', 'max:255'],
            'mistral_api_key'       => ['nullable', 'string'],
            'mistral_model'         => ['nullable', 'string', 'max:120'],
            'ollama_base_url'       => ['nullable', 'url', 'max:255'],
            'ollama_model'          => ['nullable', 'string', 'max:120'],
        ];

        // Champs dynamiques par tâche.
        foreach (array_keys(self::TASKS) as $task) {
            $rules["task_{$task}_driver"] = ['nullable', 'string', 'in:' . implode(',', $allowed)];
            $rules["task_{$task}_model"]  = ['nullable', 'string', 'max:120'];
        }

        $data = $request->validate($rules);

        $changedKeys = [];
        foreach ($data as $key => $value) {
            // Ne pas écraser une clé chiffrée si l'user n'a rien saisi
            // (l'affichage renvoie '••••••••' mais on l'ignore)
            if (in_array($key, self::ENCRYPTED_KEYS, true)) {
                if (blank($value) || $value === '••••••••') continue;
                Setting::set('ai', $key, $value, true);
                $changedKeys[] = $key; // le nom de la clé, jamais sa valeur
            } else {
                // null = champ non envoyé → on ne touche pas ; '' = effacement explicite.
                if ($value !== null) {
                    Setting::set('ai', $key, $value, false);
                    $changedKeys[] = $key;
                }
            }
        }

        // Traçabilité : quels réglages ont changé (noms uniquement, pas les secrets).
        \App\Models\AuditLog::record('settings.updated', null, ['keys' => $changedKeys]);

        return back()->with('success', 'Configuration IA enregistrée.');
    }

    /**
     * Teste la connexion au fournisseur IA sélectionné avec la configuration
     * réellement enregistrée (clé déchiffrée côté serveur, jamais exposée).
     * Une clé invalide se découvre ici, pas au premier candidat sans synthèse.
     */
    public function testConnection(Request $request, \Praxis\Core\AI\AIManager $ai)
    {
        $data = $request->validate([
            'driver' => ['required', 'string', 'in:anthropic,anthropic_haiku,openai,deepseek,mistral,ollama'],
        ]);

        try {
            // Ping minimal : une génération d'un mot suffit à valider clé + modèle + réseau.
            $driver   = $ai->driver($data['driver']);
            $response = $driver->generate('Réponds uniquement : OK', ['max_tokens' => 8]);

            return back()->with('success', sprintf(
                'Connexion %s réussie (modèle %s) — réponse : « %s »',
                $data['driver'],
                $driver->model(),
                \Illuminate\Support\Str::limit(trim($response), 40),
            ));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', sprintf(
                'Échec de connexion %s : %s',
                $data['driver'],
                \Illuminate\Support\Str::limit($e->getMessage(), 180),
            ));
        }
    }
}
