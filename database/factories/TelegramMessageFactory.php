<?php

namespace Database\Factories;

use App\Models\TelegramMessage;
use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TelegramMessage>
 */
class TelegramMessageFactory extends Factory
{
    protected $model = TelegramMessage::class;

    public function definition(): array
    {
        return [
            'telegram_user_id' => TelegramUser::factory(),
            'telegram_chat_id' => (string) fake()->randomNumber(9, true),
            'telegram_message_id' => (string) fake()->randomNumber(6, true),
            'text' => fake()->sentence(),
            'raw_payload' => [],
        ];
    }
}
