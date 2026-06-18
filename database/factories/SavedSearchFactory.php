<?php

namespace Database\Factories;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'filters' => [
                'city' => fake()->randomElement(['Cairo', 'Giza', 'Alexandria']),
                'listing_type' => fake()->randomElement(['sale', 'rent']),
            ],
            'notify' => fake()->boolean(50),
            'last_notified_at' => null,
        ];
    }
}
