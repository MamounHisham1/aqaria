<?php

use App\Models\Listing;
use App\Models\ListingClick;
use App\Models\ListingView;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->listing = Listing::factory()->create([
        'is_active' => true,
        'city' => 'Cairo',
        'images' => ['https://example.com/img1.jpg', 'https://example.com/img2.jpg'],
        'amenities' => ['Pool', 'Gym', 'Parking'],
    ]);

    $this->inactiveListing = Listing::factory()->create(['is_active' => false]);
});

// ========== Show Page ==========

test('show page returns listing detail', function () {
    $response = $this->get(route('listings.show', $this->listing));

    $response->assertOk();
    $response->assertInertia(
        fn($page) => $page
            ->has('listing')
            ->where('listing.id', $this->listing->id)
            ->where('listing.title', $this->listing->title)
    );
});

test('show page returns related listings from same city', function () {
    Listing::factory()->count(3)->create(['city' => 'Cairo', 'is_active' => true]);
    Listing::factory()->count(2)->create(['city' => 'Alexandria', 'is_active' => true]);

    $response = $this->get(route('listings.show', $this->listing));

    $response->assertInertia(
        fn($page) => $page
            ->has('relatedListings', 3)
    );
});

test('show page returns 404 for inactive listing', function () {
    $response = $this->get(route('listings.show', $this->inactiveListing));

    $response->assertNotFound();
});

// ========== View Tracking ==========

test('show page records a view for the listing', function () {
    $visitorId = bin2hex(random_bytes(16));

    $response = $this->withCookie('visitor_id', $visitorId)
        ->get(route('listings.show', $this->listing));

    $response->assertOk();

    expect(ListingView::where('listing_id', $this->listing->id)->count())->toBe(1);
    expect(ListingView::where('listing_id', $this->listing->id)->first()->visitor_id)->toBe($visitorId);
});

test('show page deduplicates views within 24 hours', function () {
    $visitorId = bin2hex(random_bytes(16));

    $this->withCookie('visitor_id', $visitorId)
        ->get(route('listings.show', $this->listing));
    $this->withCookie('visitor_id', $visitorId)
        ->get(route('listings.show', $this->listing));

    expect(ListingView::where('listing_id', $this->listing->id)->count())->toBe(1);
});

test('show page allows views from different visitors', function () {
    $visitorA = bin2hex(random_bytes(16));
    $visitorB = bin2hex(random_bytes(16));

    $this->withCookie('visitor_id', $visitorA)
        ->get(route('listings.show', $this->listing));
    $this->withCookie('visitor_id', $visitorB)
        ->get(route('listings.show', $this->listing));

    expect(ListingView::where('listing_id', $this->listing->id)->count())->toBe(2);
});

// ========== Click Tracking ==========

test('recordClick records a phone click', function () {
    $visitorId = bin2hex(random_bytes(16));

    $response = $this->withCookie('visitor_id', $visitorId)
        ->postJson(route('listings.click', $this->listing), [
            'click_type' => 'phone',
        ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    expect(ListingClick::where('listing_id', $this->listing->id)->count())->toBe(1);
    expect(ListingClick::first()->click_type)->toBe('phone');
    expect(ListingClick::first()->visitor_id)->toBe($visitorId);
});

test('recordClick records a whatsapp click', function () {
    $this->withCookie('visitor_id', bin2hex(random_bytes(16)))
        ->postJson(route('listings.click', $this->listing), [
            'click_type' => 'whatsapp',
        ])
        ->assertOk();

    expect(ListingClick::where('click_type', 'whatsapp')->count())->toBe(1);
});

test('recordClick records a detail click', function () {
    $this->withCookie('visitor_id', bin2hex(random_bytes(16)))
        ->postJson(route('listings.click', $this->listing), [
            'click_type' => 'detail',
        ])
        ->assertOk();

    expect(ListingClick::where('click_type', 'detail')->count())->toBe(1);
});

test('recordClick validates click_type is required', function () {
    $response = $this->withCookie('visitor_id', bin2hex(random_bytes(16)))
        ->postJson(route('listings.click', $this->listing), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['click_type']);
});

test('recordClick validates click_type is valid enum', function () {
    $response = $this->withCookie('visitor_id', bin2hex(random_bytes(16)))
        ->postJson(route('listings.click', $this->listing), [
            'click_type' => 'invalid',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['click_type']);
});

test('recordClick does not record click without visitor cookie', function () {
    $response = $this->postJson(route('listings.click', $this->listing), [
        'click_type' => 'phone',
    ]);

    $response->assertOk();
    expect(ListingClick::count())->toBe(0);
});

// ========== Response Data ==========

test('show page includes views and clicks count', function () {
    ListingView::factory()->count(5)->create(['listing_id' => $this->listing->id]);
    ListingClick::factory()->count(3)->create(['listing_id' => $this->listing->id]);

    $response = $this->get(route('listings.show', $this->listing));

    $response->assertInertia(
        fn($page) => $page
            ->where('listing.views_count', 5)
            ->where('listing.clicks_count', 3)
    );
});

test('show page includes listing amenities and images', function () {
    $response = $this->get(route('listings.show', $this->listing));

    $response->assertInertia(
        fn($page) => $page
            ->where('listing.images', ['https://example.com/img1.jpg', 'https://example.com/img2.jpg'])
            ->where('listing.amenities', ['Pool', 'Gym', 'Parking'])
    );
});
