<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Contracts\ChatClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaChatClient implements ChatClient
{
    public function create(array $params): array
    {
        $baseUrl = rtrim(config('services.ollama.base_url', 'http://localhost:11434'), '/');
        $model = config('services.ollama.model', 'llama3.1');

        $payload = [
            'model' => $model,
            'messages' => $params['messages'] ?? [],
            'tools' => $params['tools'] ?? [],
            'stream' => false,
        ];

        if (! empty($params['tools'])) {
            $payload['tool_choice'] = 'auto';
        }

        try {
            $response = Http::timeout(120)
                ->post("{$baseUrl}/api/chat", $payload);

            $response->throw();

            $data = $response->json();
        } catch (\Exception $e) {
            Log::error('Ollama request failed', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);
            throw $e;
        }

        return $this->normalizeResponse($data);
    }

    private function normalizeResponse(array $data): array
    {
        $message = $data['message'] ?? [];
        $toolCalls = $message['tool_calls'] ?? [];
        $normalizedToolCalls = [];

        foreach ($toolCalls as $index => $toolCall) {
            $function = $toolCall['function'] ?? [];
            $arguments = $function['arguments'] ?? [];

            $normalizedToolCalls[] = [
                'id' => 'call_' . ($index + 1),
                'type' => 'function',
                'function' => [
                    'name' => $function['name'] ?? '',
                    'arguments' => is_array($arguments)
                        ? json_encode($arguments)
                        : ($arguments ?? '{}'),
                ],
            ];
        }

        return [
            'choices' => [
                [
                    'message' => [
                        'content' => $message['content'] ?? null,
                        'tool_calls' => $normalizedToolCalls ?: null,
                    ],
                    'finish_reason' => $message['tool_calls'] ? 'tool_calls' : 'stop',
                ],
            ],
        ];
    }
}
