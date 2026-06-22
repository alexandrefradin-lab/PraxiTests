<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\Listeners\AwardXpOnAnswer;

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
        AwardXpOnAnswer::register($this->app->make(GamificationEngine::class));

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
            ];

            foreach ($drivers as $driver => $fields) {
                foreach ($fields as $field) {
                    if ($value = $get("{$driver}_{$field}")) {
                        config(["ai.drivers.{$driver}.{$field}" => $value]);
                    }
                }
            }

            if ($url = $get('ollama_base_url')) {
                config(['ai.drivers.ollama.base_url' => $url]);
            }
            if ($model = $get('ollama_model')) {
                config(['ai.drivers.ollama.model' => $model]);
            }

        } catch (\Throwable) {
            // DB pas encore prête (phase install) — on ignore silencieusement.
        }
    }
}
