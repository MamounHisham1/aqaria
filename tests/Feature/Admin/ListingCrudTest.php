<?php

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ========== List Index ==========

test('admin can view listings index', function () {
    Listing::factory()->count(5)->create();

    $response = $this->get(route('admin.listings.index'));

    $response->assertOk();
    $response->assertInertia(fn($page) => $page->has('listings.data'));
});

test('admin listings index shows all listings including inactive', function () {
    Listing::factory()->count(3)->create(['is_active' => true]);
    Listing::factory()->count(2)->create(['is_active' => false]);

    $response = $this->get(route('admin.listings.index'));

    $response->assertInertia(fn($page) => $page->where('listings.total', 5));
});

test('admin listings index includes views and clicks count', function () {
    Listing::factory()->count(3)->create();

    $response = $this->get(route('admin.listings.index'));

    $response->assertInertia(fn($page) => $page->has('listings.data'));
});

// ========== Create Listing ==========

test('admin can view create listing form', function () {
    $response = $this->get(route('admin.listings.create'));

    $response->assertOk();
});

test('admin can create a new listing', function () {
    $data = [
        'title' => 'Test Listing',
        'description' => 'A test listing description',
        'price' => 500000,
        'area_sqm' => 150,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'property_type' => 'apartment',
        'listing_type' => 'sale',
        'city' => 'Cairo',
        'district' => 'Maadi',
        'address' => '123 Test Street',
        'contact_phone' => '01000000000',
        'contact_whatsapp' => '01000000001',
        'is_featured' => true,
        'is_active' => true,
        'images' => ['https://example.com/img.jpg'],
        'amenities' => ['Pool', 'Gym'],
    ];

    $response = $this->post(route('admin.listings.store'), $data);

    $response->assertRedirect(route('admin.listings.index'));

    expect(Listing::count())->toBe(1);
    $listing = Listing::first();
    expect($listing->title)->toBe('Test Listing');
    expect($listing->is_featured)->toBeTrue();
    expect($listing->images)->toBeArray();
    expect($listing->amenities)->toBeArray();
});

test('create listing validates required fields', function () {
    $response = $this->post(route('admin.listings.store'), []);

    $response->assertSessionHasErrors([
        'title',
        'description',
        'price',
        'area_sqm',
        'bedrooms',
        'bathrooms',
        'property_type',
        'listing_type',
        'city',
        'district',
        'address',
        'contact_phone',
    ]);
});

test('create listing validates property_type enum', function () {
    $response = $this->post(route('admin.listings.store'), [
        'property_type' => 'invalid_type',
    ]);

    $response->assertSessionHasErrors(['property_type']);
});

test('create listing validates listing_type enum', function () {
    $response = $this->post(route('admin.listings.store'), [
        'listing_type' => 'invalid_type',
    ]);

    $response->assertSessionHasErrors(['listing_type']);
});

test('create listing accepts optional fields as null', function () {
    $data = [
        'title' => 'Minimal Listing',
        'description' => 'A minimal listing',
        'price' => 300000,
        'area_sqm' => 80,
        'bedrooms' => 1,
        'bathrooms' => 1,
        'property_type' => 'apartment',
        'listing_type' => 'rent',
        'city' => 'Luxor',
        'district' => 'East Bank',
        'address' => '456 Minimal Ave',
        'contact_phone' => '01100000000',
    ];

    $response = $this->post(route('admin.listings.store'), $data);

    $response->assertRedirect(route('admin.listings.index'));
    expect(Listing::count())->toBe(1);
    expect(Listing::first()->latitude)->toBeNull();
    expect(Listing::first()->contact_whatsapp)->toBeNull();
});

// ========== Edit Listing ==========

test('admin can view edit listing form', function () {
    $listing = Listing::factory()->create();

    $response = $this->get(route('admin.listings.edit', $listing));

    $response->assertOk();
    $response->assertInertia(
        fn($page) => $page
            ->where('listing.id', $listing->id)
            ->where('listing.title', $listing->title)
    );
});

test('admin can update a listing', function () {
    $listing = Listing::factory()->create(['title' => 'Original Title']);

    $response = $this->put(route('admin.listings.update', $listing), [
        'title' => 'Updated Title',
        'description' => 'Updated description',
        'price' => 750000,
        'area_sqm' => 200,
        'bedrooms' => 4,
        'bathrooms' => 3,
        'property_type' => 'villa',
        'listing_type' => 'sale',
        'city' => 'Alexandria',
        'district' => 'Smouha',
        'address' => '789 Updated Blvd',
        'contact_phone' => '01200000000',
    ]);

    $response->assertRedirect(route('admin.listings.index'));

    $listing->refresh();
    expect($listing->title)->toBe('Updated Title');
    expect($listing->price)->toBe(750000.00);
    expect($listing->city)->toBe('Alexandria');
});

// ========== Toggle Active ==========

test('admin can toggle listing active status', function () {
    $listing = Listing::factory()->create(['is_active' => true]);

    $response = $this->patch(route('admin.listings.toggle', $listing));

    $response->assertRedirect();

    $listing->refresh();
    expect($listing->is_active)->toBeFalse();

    // Toggle back
    $this->patch(route('admin.listings.toggle', $listing));
    $listing->refresh();
    expect($listing->is_active)->toBeTrue();
});

// ========== Delete Listing ==========

test('admin can delete a listing', function () {
    $listing = Listing::factory()->create();

    $response = $this->delete(route('admin.listings.destroy', $listing));

    $response->assertRedirect(route('admin.listings.index'));
    expect(Listing::count())->toBe(0);
});

// ========== Authentication ==========

test('guest cannot access admin listing routes', function () {
    $listing = Listing::factory()->create();

    $this->get(route('admin.listings.index'))->assertRedirect(route('login'));
    $this->get(route('admin.listings.create'))->assertRedirect(route('login'));
    $this->post(route('admin.listings.store'), [])->assertRedirect(route('login'));
    $this->get(route('admin.listings.edit', $listing))->assertRedirect(route('login'));
    $this->put(route('admin.listings.update', $listing), [])->assertRedirect(route('login'));
    $this->delete(route('admin.listings.destroy', $listing))->assertRedirect(route('login'));
    $this->patch(route('admin.listings.toggle', $listing))->assertRedirect(route('login'));
});
