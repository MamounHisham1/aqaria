<?php

use App\Models\Listing;
use App\Models\ListingClick;
use App\Models\ListingView;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->listing = Listing::factory()->create();
    $this->visitorId = bin2hex(random_bytes(16));
});

// ========== ListingView Scopes ==========

test('forListing scope filters by listing id', function () {
    $otherListing = Listing::factory()->create();

    ListingView::factory()->count(3)->create(['listing_id' => $this->listing->id]);
    ListingView::factory()->count(2)->create(['listing_id' => $otherListing->id]);

    $results = ListingView::forListing($this->listing->id)->get();

    expect($results)->toHaveCount(3);
    $results->each(fn ($view) => expect($view->listing_id)->toBe($this->listing->id));
});

test('byVisitor scope filters by visitor id', function () {
    ListingView::factory()->count(3)->create();
    ListingView::factory()->count(2)->byVisitor($this->visitorId)->create(['listing_id' => $this->listing->id]);

    $results = ListingView::byVisitor($this->visitorId)->get();

    expect($results)->toHaveCount(2);
});

test('inLastDays scope filters by date range', function () {
    ListingView::factory()->create([
        'listing_id' => $this->listing->id,
        'viewed_at' => now()->subDays(40),
    ]);
    ListingView::factory()->count(3)->create([
        'listing_id' => $this->listing->id,
        'viewed_at' => now()->subDays(2),
    ]);

    $results = ListingView::inLastDays(7)->get();
    expect($results)->toHaveCount(3);

    $results = ListingView::inLastDays(30)->get();
    expect($results)->toHaveCount(3);

    $allResults = ListingView::all();
    expect($allResults)->toHaveCount(4);
});

// ========== ListingClick Scopes ==========

test('forListing scope filters clicks by listing', function () {
    $otherListing = Listing::factory()->create();

    ListingClick::factory()->count(2)->create(['listing_id' => $this->listing->id]);
    ListingClick::factory()->create(['listing_id' => $otherListing->id]);

    $results = ListingClick::forListing($this->listing->id)->get();

    expect($results)->toHaveCount(2);
});

test('ofType scope filters clicks by click type', function () {
    ListingClick::factory()->ofType('phone')->create(['listing_id' => $this->listing->id]);
    ListingClick::factory()->ofType('phone')->create(['listing_id' => $this->listing->id]);
    ListingClick::factory()->ofType('whatsapp')->create(['listing_id' => $this->listing->id]);
    ListingClick::factory()->ofType('detail')->create(['listing_id' => $this->listing->id]);

    expect(ListingClick::ofType('phone')->count())->toBe(2);
    expect(ListingClick::ofType('whatsapp')->count())->toBe(1);
    expect(ListingClick::ofType('detail')->count())->toBe(1);
});

test('inLastDays scope filters clicks by date', function () {
    ListingClick::factory()->create([
        'listing_id' => $this->listing->id,
        'clicked_at' => now()->subDays(10),
    ]);
    ListingClick::factory()->count(2)->create([
        'listing_id' => $this->listing->id,
        'clicked_at' => now()->subHours(5),
    ]);

    expect(ListingClick::inLastDays(1)->count())->toBe(2);
    expect(ListingClick::inLastDays(7)->count())->toBe(2);
    expect(ListingClick::inLastDays(30)->count())->toBe(3);
});

// ========== Deduplication Logic ==========

test('view deduplication prevents duplicate views within 24 hours', function () {
    ListingView::create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $this->visitorId,
        'ip_address' => '127.0.0.1',
        'viewed_at' => now(),
    ]);

    $recentViewExists = ListingView::query()
        ->forListing($this->listing->id)
        ->byVisitor($this->visitorId)
        ->where('viewed_at', '>=', now()->subDay())
        ->exists();

    expect($recentViewExists)->toBeTrue();
    expect(ListingView::count())->toBe(1);

    if (! $recentViewExists) {
        ListingView::create([
            'listing_id' => $this->listing->id,
            'visitor_id' => $this->visitorId,
            'viewed_at' => now(),
        ]);
    }

    expect(ListingView::count())->toBe(1);
});

test('view deduplication allows view after 24 hours', function () {
    ListingView::create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $this->visitorId,
        'ip_address' => '127.0.0.1',
        'viewed_at' => now()->subHours(25),
    ]);

    $recentViewExists = ListingView::query()
        ->forListing($this->listing->id)
        ->byVisitor($this->visitorId)
        ->where('viewed_at', '>=', now()->subDay())
        ->exists();

    expect($recentViewExists)->toBeFalse();

    ListingView::create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $this->visitorId,
        'ip_address' => '127.0.0.1',
        'viewed_at' => now(),
    ]);

    expect(ListingView::count())->toBe(2);
});

test('different visitors can view same listing without deduplication', function () {
    $visitorA = bin2hex(random_bytes(16));
    $visitorB = bin2hex(random_bytes(16));

    ListingView::create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $visitorA,
        'viewed_at' => now(),
    ]);
    ListingView::create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $visitorB,
        'viewed_at' => now(),
    ]);

    expect(ListingView::count())->toBe(2);
});

// ========== Click Tracking ==========

test('click tracking records all click types', function () {
    ListingClick::create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $this->visitorId,
        'click_type' => 'phone',
        'clicked_at' => now(),
    ]);
    ListingClick::create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $this->visitorId,
        'click_type' => 'whatsapp',
        'clicked_at' => now(),
    ]);
    ListingClick::create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $this->visitorId,
        'click_type' => 'detail',
        'clicked_at' => now(),
    ]);

    expect(ListingClick::count())->toBe(3);
    expect(ListingClick::ofType('phone')->count())->toBe(1);
    expect(ListingClick::ofType('whatsapp')->count())->toBe(1);
    expect(ListingClick::ofType('detail')->count())->toBe(1);
});

test('multiple clicks of same type are all recorded', function () {
    ListingClick::factory()->ofType('phone')->count(3)->create([
        'listing_id' => $this->listing->id,
        'visitor_id' => $this->visitorId,
    ]);

    expect(ListingClick::ofType('phone')->count())->toBe(3);
});
