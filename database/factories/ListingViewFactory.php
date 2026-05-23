<?php

namespace Database\Factories;

use App\Models\ListingView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingView>
 */
class ListingViewFactory extends Factory
{
    protected $model = ListingView::class;

    public function definition(): array
    {
        return [
            'listing_id' => \App\Models\Listing::factory(),
            'visitor_id' => bin2hex(random_bytes(16)),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'viewed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function forListing(int $listingId): static
    {
        return $this->state(fn() => [
            'listing_id' => $listingId,
        ]);
    }

    public function byVisitor(string $visitorId): static
    {
        return $this->state(fn() => [
            'visitor_id' => $visitorId,
        ]);
    }
}
