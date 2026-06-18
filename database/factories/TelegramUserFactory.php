<?php

namespace Database\Factories;

use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TelegramUser>
 */
class TelegramUserFactory extends Factory
{
    protected $model = TelegramUser::class;

    public function definition(): array
    {
        return [
            'telegram_id' => (string) fake()->unique()->randomNumber(9, true),
            'username' => fake()->userName(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'is_authorized' => false,
            'role' => 'user',
            'last_interaction_at' => now(),
        ];
    }

    public function authorized(): static
    {
        return $this->state(fn () => [
            'is_authorized' => true,
            'role' => 'admin',
        ]);
    }
}
