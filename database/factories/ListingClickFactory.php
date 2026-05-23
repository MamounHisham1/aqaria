<?php

namespace Database\Factories;

use App\Models\ListingClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingClick>
 */
class ListingClickFactory extends Factory
{
    protected $model = ListingClick::class;

    public function definition(): array
    {
        return [
            'listing_id' => \App\Models\Listing::factory(),
            'visitor_id' => bin2hex(random_bytes(16)),
            'click_type' => fake()->randomElement(['phone', 'whatsapp', 'detail']),
            'clicked_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function forListing(int $listingId): static
    {
        return $this->state(fn() => [
            'listing_id' => $listingId,
        ]);
    }

    public function ofType(string $type): static
    {
        return $this->state(fn() => [
            'click_type' => $type,
        ]);
    }
}
