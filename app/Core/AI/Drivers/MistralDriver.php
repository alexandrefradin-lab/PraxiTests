<?php

namespace Praxis\Core\AI\Drivers;

use Illuminate\Support\Facades\Http;

class MistralDriver extends AbstractDriver
{
    public function key(): string
    {
        return 'mistral';
    }

    public function generate(string $prompt, array $options = []): string
    {
        return $this->chat([['role' => 'user', 'content' => $prompt]], $options);
    }

    public function chat(array $messages, array $options = []): string
    {
        $payload = [
            'model'       => $options['model'] ?? $this->config['model'],
            'messages'    => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $response = Http::withToken($this->config['api_key'])
            ->timeout(120)
            ->post('https://api.mistral.ai/v1/chat/completions', $payload);

        if ($response->failed()) {
            throw new \RuntimeException("Mistral API error: " . $response->body());
        }

        $data = $response->json();
        $this->usage = [
            'input_tokens'  => $data['usage']['prompt_tokens']     ?? 0,
            'output_tokens' => $data['usage']['completion_tokens'] ?? 0,
        ];
        return $data['choices'][0]['message']['content'] ?? '';
    }
}
