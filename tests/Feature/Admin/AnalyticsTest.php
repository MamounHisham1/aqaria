<?php

use App\Models\Listing;
use App\Models\ListingClick;
use App\Models\ListingView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ========== Dashboard Access ==========

test('admin can view analytics page', function () {
    $response = $this->get(route('admin.analytics'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('viewsOverTime')
        ->has('clicksByType')
        ->has('topListings')
        ->has('totalViews')
        ->has('totalClicks')
        ->has('selectedDays')
    );
});

test('analytics defaults to 30 days', function () {
    $response = $this->get(route('admin.analytics'));

    $response->assertInertia(fn ($page) => $page
        ->where('selectedDays', 30)
    );
});

test('analytics accepts custom day range', function () {
    $response = $this->get(route('admin.analytics', ['days' => 7]));

    $response->assertInertia(fn ($page) => $page
        ->where('selectedDays', '7')
    );
});

// ========== Views Data ==========

test('analytics returns views over time', function () {
    $listing = Listing::factory()->create();

    ListingView::factory()->count(3)->create([
        'listing_id' => $listing->id,
        'viewed_at' => now()->subDays(5),
    ]);
    ListingView::factory()->count(2)->create([
        'listing_id' => $listing->id,
        'viewed_at' => now()->subDays(2),
    ]);

    $response = $this->get(route('admin.analytics'));

    $response->assertInertia(fn ($page) => $page
        ->where('totalViews', 5)
        ->has('viewsOverTime')
    );
});

// ========== Clicks Data ==========

test('analytics returns clicks by type', function () {
    $listing = Listing::factory()->create();

    ListingClick::factory()->ofType('phone')->count(3)->create(['listing_id' => $listing->id]);
    ListingClick::factory()->ofType('whatsapp')->count(2)->create(['listing_id' => $listing->id]);
    ListingClick::factory()->ofType('detail')->count(1)->create(['listing_id' => $listing->id]);

    $response = $this->get(route('admin.analytics'));

    $response->assertInertia(fn ($page) => $page
        ->where('totalClicks', 6)
        ->has('clicksByType')
    );
});

// ========== Top Listings ==========

test('analytics returns top listings by views', function () {
    $topListing = Listing::factory()->create(['title' => 'Top Listing']);
    $lowListing = Listing::factory()->create(['title' => 'Low Listing']);

    ListingView::factory()->count(10)->create(['listing_id' => $topListing->id]);
    ListingView::factory()->count(2)->create(['listing_id' => $lowListing->id]);

    $response = $this->get(route('admin.analytics'));

    $response->assertInertia(fn ($page) => $page
        ->has('topListings', 2)
        ->where('topListings.0.title', 'Top Listing')
        ->where('topListings.0.views_count', 10)
    );
});

// ========== Date Filtering ==========

test('analytics filters data by days parameter', function () {
    $listing = Listing::factory()->create();

    // Old data (40 days ago) - excluded from 30-day window
    ListingView::factory()->count(5)->create([
        'listing_id' => $listing->id,
        'viewed_at' => now()->subDays(40),
    ]);
    ListingClick::factory()->count(3)->create([
        'listing_id' => $listing->id,
        'clicked_at' => now()->subDays(40),
    ]);

    // Recent data (last 5 days)
    ListingView::factory()->count(2)->create([
        'listing_id' => $listing->id,
        'viewed_at' => now()->subDays(2),
    ]);
    ListingClick::factory()->count(1)->create([
        'listing_id' => $listing->id,
        'clicked_at' => now()->subDays(2),
    ]);

    // 30 days - only the 2 recent views (old ones at 40 days are excluded)
    $response = $this->get(route('admin.analytics', ['days' => 30]));
    $response->assertInertia(fn ($page) => $page
        ->where('totalViews', 2)
    );

    // 7 days - only recent views within 7 days
    $response = $this->get(route('admin.analytics', ['days' => 7]));
    $response->assertInertia(fn ($page) => $page
        ->where('totalViews', 2)
        ->where('totalClicks', 1)
    );
});

// ========== Empty State ==========

test('analytics handles empty data gracefully', function () {
    $response = $this->get(route('admin.analytics'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('totalViews', 0)
        ->where('totalClicks', 0)
        ->has('viewsOverTime', 0)
        ->has('clicksByType', 0)
        ->has('topListings', 0)
    );
});
