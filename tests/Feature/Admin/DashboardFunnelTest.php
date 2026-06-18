<?php

use App\Models\Lead;
use App\Models\Listing;
use App\Models\ListingClick;
use App\Models\ListingView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($this->admin);
});

test('dashboard exposes the conversion funnel', function () {
    $listing = Listing::factory()->create(['is_active' => true]);

    ListingView::factory()->count(10)->create(['listing_id' => $listing->id]);
    ListingClick::factory()->count(4)->create(['listing_id' => $listing->id]);
    Lead::factory()->count(2)->statusNew()->create(['listing_id' => $listing->id]);
    Lead::factory()->create(['listing_id' => $listing->id, 'status' => 'closed']);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Dashboard')
        ->where('funnel.views', 10)
        ->where('funnel.clicks', 4)
        ->where('funnel.leads', 3)
        ->where('funnel.closed', 1)
        ->where('stats.totalLeads', 3)
        ->where('stats.closedLeads', 1)
        ->has('recentLeads')
        ->where('avgTimeToContactHours', null)
    );
});

test('dashboard computes average time to first contact in hours', function () {
    Carbon::setTestNow(now());

    $listing = Listing::factory()->create();

    // Lead contacted 2 hours after submission.
    Lead::factory()->create([
        'listing_id' => $listing->id,
        'created_at' => now()->subHours(5),
        'contacted_at' => now()->subHours(3),
    ]);

    // Lead contacted 6 hours after submission.
    Lead::factory()->create([
        'listing_id' => $listing->id,
        'created_at' => now()->subHours(10),
        'contacted_at' => now()->subHours(4),
    ]);

    // Uncontacted lead should be ignored.
    Lead::factory()->create([
        'listing_id' => $listing->id,
        'contacted_at' => null,
    ]);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk();
    // (2h + 6h) / 2 = 4h
    $response->assertInertia(fn ($page) => $page->where('avgTimeToContactHours', 4));

    Carbon::setTestNow();
});

test('dashboard recent leads include listing context', function () {
    $listing = Listing::factory()->create(['title' => 'Pyramid View Apartment']);
    Lead::factory()->create([
        'listing_id' => $listing->id,
        'name' => 'Omar Hassan',
        'status' => 'new',
    ]);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('recentLeads.0.name', 'Omar Hassan')
        ->where('recentLeads.0.listing.title', 'Pyramid View Apartment')
    );
});
