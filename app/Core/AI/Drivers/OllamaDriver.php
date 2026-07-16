<?php

namespace Praxis\Core\AI\Drivers;

use Illuminate\Support\Facades\Http;

class OllamaDriver extends AbstractDriver
{
    public function key(): string
    {
        return 'ollama';
    }

    public function generate(string $prompt, array $options = []): string
    {
        $response = Http::timeout($this->timeout($options, 180))->post(
            rtrim($this->config['base_url'], '/') . '/api/generate',
            [
                'model'  => $options['model'] ?? $this->config['model'],
                'prompt' => $prompt,
                'stream' => false,
            ]
        );

        if ($response->failed()) {
            throw new \RuntimeException("Ollama error (HTTP {$response->status()})");
        }

        $data = $response->json();
        $this->usage = [
            'input_tokens'  => $data['prompt_eval_count'] ?? 0,
            'output_tokens' => $data['eval_count']        ?? 0,
        ];
        return $data['response'] ?? '';
    }

    public function chat(array $messages, array $options = []): string
    {
        $response = Http::timeout($this->timeout($options, 180))->post(
            rtrim($this->config['base_url'], '/') . '/api/chat',
            [
                'model'    => $options['model'] ?? $this->config['model'],
                'messages' => $messages,
                'stream'   => false,
            ]
        );

        if ($response->failed()) {
            throw new \RuntimeException("Ollama error (HTTP {$response->status()})");
        }

        $data = $response->json();
        return $data['message']['content'] ?? '';
    }
}
