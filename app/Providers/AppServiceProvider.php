<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\Listeners\AwardXpOnAnswer;
use Praxis\Core\Mailing\Listeners\TriggerEmailSequences;
use Praxis\Core\Mailing\Services\SequenceRunner;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registre des bibliothèques d'exercices (Salle du Trésor).
        // Chaque plugin « mini-app » s'y enregistre dans son boot().
        $this->app->singleton(\Praxis\Core\Library\ExerciseLibrary::class);
    }

    public function boot(): void
    {
        // Transport Brevo API (HTTP) — contourne le blocage SMTP OVH.
        // BREVO_API_KEY est lu via config/services.php (PAS env() : avec la
        // config cachée en prod, env() renvoie null → clé vide silencieuse).
        config(['mail.mailers.brevo' => ['transport' => 'brevo']]);
        Mail::extend('brevo', fn() => new BrevoApiTransport((string) config('services.brevo.key', '')));

        AwardXpOnAnswer::register($this->app->make(GamificationEngine::class));

        // Câble les séquences email aux événements du parcours (cf. audit Fo-4).
        TriggerEmailSequences::register($this->app->make(SequenceRunner::class));

        // Charger les paramètres IA depuis la DB et les injecter dans la config.
        // Les valeurs DB ont priorité sur le .env, ce qui permet à l'admin
        // de changer de clé / de driver sans toucher au serveur.
        $this->overrideAiConfigFromDatabase();
    }

    private function overrideAiConfigFromDatabase(): void
    {
        try {
            if (!Schema::hasTable('settings')) return;

            $settings = \DB::table('settings')->where('group', 'ai')->get()->keyBy('key');

            $get = function (string $key) use ($settings): ?string {
                $row = $settings->get($key);
                if (!$row || blank($row->value)) return null;
                return $row->encrypted
                    ? \Illuminate\Support\Facades\Crypt::decryptString($row->value)
                    : $row->value;
            };

            if ($default = $get('default_driver')) {
                config(['ai.default' => $default]);
            }

            $drivers = [
                'anthropic' => ['api_key', 'model'],
                'openai'    => ['api_key', 'model'],
                'mistral'   => ['api_key', 'model'],
                'deepseek'  => ['api_key', 'model', 'base_url'],
            ];

            foreach ($drivers as $driver => $fields) {
                foreach ($fields as $field) {
                    if ($value = $get("{$driver}_{$field}")) {
                        config(["ai.drivers.{$driver}.{$field}" => $value]);
                    }
                }
            }

            // Driver Haiku (économique) : partage la clé Anthropic, mais a son propre
            // modèle réglable en admin (anthropic_haiku_model).
            if ($anthropicKey = $get('anthropic_api_key')) {
                config(['ai.drivers.anthropic_haiku.api_key' => $anthropicKey]);
            }
            if ($haikuModel = $get('anthropic_haiku_model')) {
                config(['ai.drivers.anthropic_haiku.model' => $haikuModel]);
            }

            if ($url = $get('ollama_base_url')) {
                config(['ai.drivers.ollama.base_url' => $url]);
            }
            if ($model = $get('ollama_model')) {
                config(['ai.drivers.ollama.model' => $model]);
            }

            // ── Modèle PAR TÂCHE (réglage admin) ────────────────────────────────
            // Pour chaque tâche IA, l'admin peut choisir un fournisseur (driver) et,
            // optionnellement, un modèle précis. task_<tache>_driver / task_<tache>_model.
            foreach (array_keys((array) config('ai.tasks', [])) as $task) {
                if ($d = $get("task_{$task}_driver")) {
                    config(["ai.tasks.{$task}.driver" => $d]);
                }
                if ($m = $get("task_{$task}_model")) {
                    config(["ai.tasks.{$task}.model" => $m]);
                }
            }

        } catch (\Throwable) {
            // DB pas encore prête (phase install) — on ignore silencieusement.
        }
    }
}
