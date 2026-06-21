<?php

namespace Praxis\Core\AI\Drivers;

use Praxis\Core\AI\Contracts\AIDriverContract;

abstract class AbstractDriver implements AIDriverContract
{
    protected array $config;
    protected array $usage = ['input_tokens' => 0, 'output_tokens' => 0];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function lastUsage(): array
    {
        return $this->usage;
    }

    /**
     * Implémentation par défaut : exécution SÉQUENTIELLE (pas de parallélisme).
     * Les drivers capables de concurrence (ex : AnthropicDriver via Http::pool)
     * surchargent cette méthode. L'usage est cumulé sur l'ensemble du lot.
     */
    public function chatMany(array $batch, array $options = []): array
    {
        $out   = [];
        $total = ['input_tokens' => 0, 'output_tokens' => 0];

        foreach ($batch as $key => $req) {
            $out[$key] = $this->chat(
                $req['messages'] ?? [],
                array_merge($options, $req['options'] ?? []),
            );

            $u = $this->lastUsage();
            $total['input_tokens']  += $u['input_tokens']  ?? 0;
            $total['output_tokens'] += $u['output_tokens'] ?? 0;
        }

        $this->usage = $total;

        return $out;
    }

    /**
     * Retourne le nom du modèle exact configuré pour ce driver.
     * Utilisé pour la traçabilité IA dans ai_model sur TestResult.
     */
    public function model(): string
    {
        return $this->config['model'] ?? $this->key();
    }

    public function generateJson(string $prompt, array $schema = [], array $options = []): array
    {
        $instruction = "Réponds STRICTEMENT en JSON valide, sans texte avant ni après, sans bloc ```json.";
        if ($schema) {
            $instruction .= "\nSchéma attendu : " . json_encode($schema, JSON_UNESCAPED_UNICODE);
        }
        $raw = $this->generate($instruction . "\n\n" . $prompt, $options);
        $clean = $this->extractJson($raw);
        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException("AI did not return valid JSON: " . substr($raw, 0, 300));
        }
        return $decoded;
    }

    protected function extractJson(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*(.+?)\s*```/s', $raw, $m)) {
            return $m[1];
        }
        $first = strpos($raw, '{');
        $last  = strrpos($raw, '}');
        if ($first !== false && $last !== false && $last > $first) {
            return substr($raw, $first, $last - $first + 1);
        }
        return $raw;
    }
}
