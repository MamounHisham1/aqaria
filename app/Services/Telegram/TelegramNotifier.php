<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends outbound messages through the configured Telegram bot.
 *
 * Extracted from the webhook controller so application events (e.g. a new
 * lead) can notify the admin chat without duplicating the HTTP plumbing.
 */
class TelegramNotifier
{
    public function send(string $chatId, string $text): void
    {
        $token = config('services.telegram.bot_token');

        if (empty($token) || empty($chatId)) {
            return;
        }

        try {
            Http::post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', ['error' => $e->getMessage(), 'chat_id' => $chatId]);
        }
    }

    /**
     * Notify the configured admin chat, if any.
     */
    public function notifyAdmin(string $text): void
    {
        $chatId = config('services.telegram.admin_chat_id');

        if (empty($chatId)) {
            return;
        }

        $this->send($chatId, $text);
    }
}
