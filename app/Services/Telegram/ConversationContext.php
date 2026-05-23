<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Cache;

class ConversationContext
{
    private const CACHE_PREFIX = 'telegram_conversation_';
    private const TTL_MINUTES = 30;

    public function getMessages(string $chatId): array
    {
        return Cache::get($this->key($chatId), []);
    }

    public function addMessage(string $chatId, string $role, string $content): void
    {
        $messages = $this->getMessages($chatId);
        $messages[] = ['role' => $role, 'content' => $content];

        if (count($messages) > 20) {
            $messages = array_slice($messages, -20);
        }

        Cache::put($this->key($chatId), $messages, now()->addMinutes(self::TTL_MINUTES));
    }

    public function addAssistantMessageWithTool(string $chatId, string $content, array $toolCalls): void
    {
        $messages = $this->getMessages($chatId);
        $messages[] = [
            'role' => 'assistant',
            'content' => $content,
            'tool_calls' => $toolCalls,
        ];

        if (count($messages) > 20) {
            $messages = array_slice($messages, -20);
        }

        Cache::put($this->key($chatId), $messages, now()->addMinutes(self::TTL_MINUTES));
    }

    public function addToolResult(string $chatId, string $toolCallId, string $content): void
    {
        $messages = $this->getMessages($chatId);
        $messages[] = [
            'role' => 'tool',
            'tool_call_id' => $toolCallId,
            'content' => $content,
        ];

        Cache::put($this->key($chatId), $messages, now()->addMinutes(self::TTL_MINUTES));
    }

    public function clear(string $chatId): void
    {
        Cache::forget($this->key($chatId));
    }

    public function setLastListingId(string $chatId, int $listingId): void
    {
        Cache::put($this->key($chatId) . '_last_listing', $listingId, now()->addMinutes(self::TTL_MINUTES));
    }

    public function getLastListingId(string $chatId): ?int
    {
        return Cache::get($this->key($chatId) . '_last_listing');
    }

    private function key(string $chatId): string
    {
        return self::CACHE_PREFIX . $chatId;
    }
}
