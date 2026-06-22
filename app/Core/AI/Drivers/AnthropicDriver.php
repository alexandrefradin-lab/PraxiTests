<?php

namespace Praxis\Core\AI\Drivers;

use Illuminate\Support\Facades\Http;

class AnthropicDriver extends AbstractDriver
{
    public function key(): string
    {
        return 'anthropic';
    }

    public function generate(string $prompt, array $options = []): string
    {
        return $this->chat([
            ['role' => 'user', 'content' => $prompt],
        ], $options);
    }

    public function chat(array $messages, array $options = []): string
    {
        $payload = $this->buildPayload($messages, $options);

        $response = $this->headers()
            ->timeout(120)
            ->post('https://api.anthropic.com/v1/messages', $payload);

        if ($response->failed()) {
            throw new \RuntimeException("Anthropic API error: " . $response->body());
        }

        $data = $response->json();
        $this->usage = [
            'input_tokens'  => $data['usage']['input_tokens']  ?? 0,
            'output_tokens' => $data['usage']['output_tokens'] ?? 0,
        ];

        return $this->extractText($data);
    }

    /**
     * Exécute plusieurs requêtes EN PARALLÈLE via Http::pool (Guzzle async).
     * C'est le levier qui accélère le Grimoire : synthèse et voies sont générées
     * en même temps au lieu de l'une après l'autre. L'usage est cumulé sur le lot.
     */
    public function chatMany(array $batch, array $options = []): array
    {
        // Un seul élément (ou aucun) → inutile de mobiliser le pool.
        if (count($batch) <= 1) {
            return parent::chatMany($batch, $options);
        }

        $keys = array_keys($batch);

        try {
            $responses = Http::pool(function ($pool) use ($batch, $options) {
                $requests = [];
                foreach ($batch as $key => $req) {
                    $payload = $this->buildPayload(
                        $req['messages'] ?? [],
                        array_merge($options, $req['options'] ?? []),
                    );

                    $requests[] = $pool->as((string) $key)
                        ->withHeaders($this->headerArray())
                        ->timeout(120)
                        ->post('https://api.anthropic.com/v1/messages', $payload);
                }
                return $requests;
            });
        } catch (\Throwable $e) {
            // Le pool lui-même a cassé → repli séquentiel (voir ci-dessous).
            return parent::chatMany($batch, $options);
        }

        // Robustesse OVH mutualisé : l'hébergement refuse parfois les connexions
        // sortantes SIMULTANÉES (cURL error 7 « Connection refused »). Dans ce cas
        // une (ou plusieurs) réponses du pool sont des exceptions de connexion. On
        // bascule alors sur l'exécution SÉQUENTIELLE (un appel après l'autre), qui
        // passe sans souci. On ne fait ce repli que pour les erreurs de CONNEXION ;
        // une vraie erreur HTTP de l'API (4xx/5xx) ne serait pas résolue ainsi.
        foreach ($keys as $key) {
            if (($responses[(string) $key] ?? null) instanceof \Throwable) {
                return parent::chatMany($batch, $options);
            }
        }

        $this->usage = ['input_tokens' => 0, 'output_tokens' => 0];
        $out = [];

        foreach ($keys as $key) {
            $resp = $responses[(string) $key] ?? null;

            if (!$resp || $resp->failed()) {
                throw new \RuntimeException("Anthropic API error (pool {$key}): " . ($resp ? $resp->body() : 'no response'));
            }

            $data = $resp->json();
            $this->usage['input_tokens']  += $data['usage']['input_tokens']  ?? 0;
            $this->usage['output_tokens'] += $data['usage']['output_tokens'] ?? 0;

            $out[$key] = $this->extractText($data);
        }

        return $out;
    }

    /** Construit le payload de l'API Messages à partir des messages + options. */
    protected function buildPayload(array $messages, array $options = []): array
    {
        $system = null;
        $clean  = [];
        foreach ($messages as $m) {
            if (($m['role'] ?? null) === 'system') {
                $system = ($system ? $system . "\n\n" : '') . $m['content'];
            } else {
                $clean[] = $m;
            }
        }

        $payload = [
            'model'       => $options['model'] ?? $this->config['model'],
            'max_tokens'  => $options['max_tokens'] ?? $this->config['max_tokens'] ?? 4000,
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
            'messages'    => $clean,
        ];
        if ($system) $payload['system'] = $system;

        return $payload;
    }

    /** En-têtes API sous forme de tableau (réutilisés par chat() et le pool). */
    protected function headerArray(): array
    {
        return [
            'x-api-key'         => $this->config['api_key'],
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ];
    }

    protected function headers()
    {
        return Http::withHeaders($this->headerArray());
    }

    /** Extrait le texte de la réponse API, en levant si vide. */
    protected function extractText(array $data): string
    {
        $text = $data['content'][0]['text'] ?? '';

        if ($text === '') {
            throw new \RuntimeException('AnthropicDriver: réponse IA vide ou inattendue.');
        }

        return $text;
    }
}
