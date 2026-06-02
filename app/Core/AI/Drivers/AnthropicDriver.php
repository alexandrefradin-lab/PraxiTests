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
        $system = null;
        $clean = [];
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

        $response = Http::withHeaders([
            'x-api-key'         => $this->config['api_key'],
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', $payload);

        if ($response->failed()) {
            throw new \RuntimeException("Anthropic API error: " . $response->body());
        }

        $data = $response->json();
        $this->usage = [
            'input_tokens'  => $data['usage']['input_tokens']  ?? 0,
            'output_tokens' => $data['usage']['output_tokens'] ?? 0,
        ];

        $text = $data['content'][0]['text'] ?? '';

        if ($text === '') {
            throw new \RuntimeException('AnthropicDriver: réponse IA vide ou inattendue.');
        }
        return $text;
    }
}
