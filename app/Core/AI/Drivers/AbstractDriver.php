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
