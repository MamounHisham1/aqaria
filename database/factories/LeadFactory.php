<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'phone' => '010'.fake()->numberBetween(0, 9).fake()->numerify('########'),
            'email' => fake()->safeEmail(),
            'message' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(Lead::STATUSES),
            'admin_notes' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'contacted_at' => null,
            'closed_at' => null,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function statusNew(): static
    {
        return $this->state(fn () => ['status' => 'new']);
    }

    public function forListing(int $listingId): static
    {
        return $this->state(fn () => ['listing_id' => $listingId]);
    }
}
