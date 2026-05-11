<?php

namespace Praxis\Core\AI\Drivers;

use Illuminate\Support\Facades\Http;

class OpenAiDriver extends AbstractDriver
{
    public function key(): string
    {
        return 'openai';
    }

    public function generate(string $prompt, array $options = []): string
    {
        return $this->chat([
            ['role' => 'user', 'content' => $prompt],
        ], $options);
    }

    public function chat(array $messages, array $options = []): string
    {
        $payload = [
            'model'       => $options['model']       ?? $this->config['model'],
            'messages'    => $messages,
            'temperature' => $options['temperature'] ?? $this->config['temperature'] ?? 0.7,
            'max_tokens'  => $options['max_tokens']  ?? $this->config['max_tokens'] ?? 2000,
        ];

        $response = Http::withToken($this->config['api_key'])
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->failed()) {
            throw new \RuntimeException("OpenAI API error: " . $response->body());
        }

        $data = $response->json();
        $this->usage = [
            'input_tokens'  => $data['usage']['prompt_tokens']     ?? 0,
            'output_tokens' => $data['usage']['completion_tokens'] ?? 0,
        ];

        return $data['choices'][0]['message']['content'] ?? '';
    }
}
