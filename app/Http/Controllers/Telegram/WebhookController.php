<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramMessage;
use App\Services\Telegram\AiAssistantService;
use App\Services\Telegram\AuthorizationService;
use App\Services\Telegram\ConversationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private AuthorizationService $authService,
        private AiAssistantService $aiService,
        private ConversationContext $context,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        if (empty($update['message'])) {
            return response()->json(['status' => 'ignored']);
        }

        $message = $update['message'];
        $chatId = (string) ($message['chat']['id'] ?? '');
        $text = $message['text'] ?? '';
        $from = $message['from'] ?? [];
        $telegramMessageId = (string) ($message['message_id'] ?? '');

        if ($chatId === '' || $text === '') {
            return response()->json(['status' => 'ignored']);
        }

        try {
            $user = $this->authService->findOrCreateUser($from);

            TelegramMessage::create([
                'telegram_user_id' => $user->id,
                'telegram_chat_id' => $chatId,
                'telegram_message_id' => $telegramMessageId,
                'text' => $text,
                'raw_payload' => $update,
            ]);

            if (strtolower(trim($text)) === '/start') {
                return $this->sendWelcome($chatId, $user);
            }

            if (strtolower(trim($text)) === '/clear') {
                $this->context->clear($chatId);
                $this->sendTelegramMessage($chatId, 'Conversation context cleared. How can I help you?');
                return response()->json(['status' => 'ok']);
            }

            $reply = $this->aiService->processMessage($chatId, $text, $user);
            $this->sendTelegramMessage($chatId, $reply);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->sendTelegramMessage($chatId, 'Sorry, something went wrong. Please try again later.');
        }

        return response()->json(['status' => 'ok']);
    }

    private function sendWelcome(string $chatId, $user): JsonResponse
    {
        $name = $user->first_name ?? 'there';
        $adminHint = $user->isAdmin() ? "\n\nYou have admin privileges — you can create, update, and delete listings." : '';

        $message = "Hello {$name}! I'm your real estate assistant. I can help you:\n"
            . "\u{2022} Search and view property listings\n"
            . "\u{2022} Get details about specific listings\n"
            . "{$adminHint}\n\n"
            . "Just tell me what you'd like to do!";

        $this->sendTelegramMessage($chatId, $message);

        return response()->json(['status' => 'ok']);
    }

    private function sendTelegramMessage(string $chatId, string $text): void
    {
        $token = config('services.telegram.bot_token');

        if (empty($token)) {
            Log::warning('Telegram bot token not configured');
            return;
        }

        try {
            \Illuminate\Support\Facades\Http::post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', ['error' => $e->getMessage()]);
        }
    }
}
