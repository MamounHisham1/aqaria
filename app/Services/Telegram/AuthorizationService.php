<?php

namespace App\Services\Telegram;

use App\Models\TelegramUser;

class AuthorizationService
{
    public function findOrCreateUser(array $from): TelegramUser
    {
        $user = TelegramUser::query()
            ->where('telegram_id', (string) $from['id'])
            ->first();

        if ($user === null) {
            $user = TelegramUser::create([
                'telegram_id' => (string) $from['id'],
                'username' => $from['username'] ?? null,
                'first_name' => $from['first_name'] ?? null,
                'last_name' => $from['last_name'] ?? null,
                'is_authorized' => false,
            ]);
        } else {
            $user->update([
                'username' => $from['username'] ?? $user->username,
                'first_name' => $from['first_name'] ?? $user->first_name,
                'last_name' => $from['last_name'] ?? $user->last_name,
                'last_interaction_at' => now(),
            ]);
        }

        return $user;
    }

    public function canQuery(TelegramUser $user): bool
    {
        return true;
    }

    public function canModify(TelegramUser $user): bool
    {
        return $user->is_authorized && $user->isAdmin();
    }

    public function requireAuthResponse(TelegramUser $user): string
    {
        return "Hello {$user->first_name}! You are not authorized to perform this action. Please contact an administrator to get access.";
    }
}
