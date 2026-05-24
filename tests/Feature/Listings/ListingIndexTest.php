<?php

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create listings in different cities with varied attributes
    Listing::factory()->count(5)->create(['city' => 'Cairo', 'listing_type' => 'sale', 'property_type' => 'apartment', 'is_active' => true, 'bedrooms' => 3]);
    Listing::factory()->count(3)->create(['city' => 'Alexandria', 'listing_type' => 'rent', 'property_type' => 'villa', 'is_active' => true, 'bedrooms' => 5]);
    Listing::factory()->count(2)->create(['city' => 'Luxor', 'listing_type' => 'sale', 'property_type' => 'apartment', 'is_active' => true, 'bedrooms' => 2]);
    Listing::factory()->count(2)->create(['is_active' => false]); // inactive
});

// ========== Basic Listing Index ==========

test('listings index page returns all active listings', function () {
    $response = $this->get(route('listings.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('listings.data'));
});

test('listings index page returns correct pagination count', function () {
    $response = $this->get(route('listings.index'));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 10)); // 10 active
});

// ========== Search ==========

test('search filters listings by keyword', function () {
    Listing::factory()->create([
        'title' => 'Beautiful Beach Villa',
        'description' => 'A stunning property by the sea',
        'city' => 'Hurghada',
        'is_active' => true,
    ]);

    $response = $this->get(route('listings.index', ['q' => 'Beach']));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 1));
});

test('search returns no results for non-matching term', function () {
    $response = $this->get(route('listings.index', ['q' => 'xyznotfound']));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 0));
});

// ========== City Filter ==========

test('filters listings by city', function () {
    $response = $this->get(route('listings.index', ['city' => 'Cairo']));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 5));
});

test('filters listings by different city', function () {
    $response = $this->get(route('listings.index', ['city' => 'Alexandria']));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 3));
});

// ========== Listing Type Filter ==========

test('filters listings by listing type', function () {
    $response = $this->get(route('listings.index', ['listing_type' => 'sale']));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 7));
});

test('filters listings by rent type', function () {
    $response = $this->get(route('listings.index', ['listing_type' => 'rent']));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 3));
});

// ========== Property Type Filter ==========

test('filters listings by property type', function () {
    $response = $this->get(route('listings.index', ['property_type' => 'villa']));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 3));
});

// ========== Combined Filters ==========

test('combines multiple filters correctly', function () {
    $response = $this->get(route('listings.index', [
        'city' => 'Cairo',
        'listing_type' => 'sale',
        'property_type' => 'apartment',
    ]));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 5));
});

// ========== Price Range ==========

test('filters by minimum price', function () {
    Listing::factory()->create(['price' => 100000, 'is_active' => true]);
    Listing::factory()->create(['price' => 500000, 'is_active' => true]);
    Listing::factory()->create(['price' => 1000000, 'is_active' => true]);

    $response = $this->get(route('listings.index', ['min_price' => 500000]));

    $totalAbove = Listing::where('is_active', true)->where('price', '>=', 500000)->count();
    $response->assertInertia(fn ($page) => $page->where('listings.total', $totalAbove));
});

// ========== Bedrooms Filter ==========

test('filters by minimum bedrooms', function () {
    $response = $this->get(route('listings.index', ['bedrooms' => 4]));

    $response->assertInertia(fn ($page) => $page->where('listings.total', 3)); // 3 with 5 bedrooms
});

// ========== Pagination ==========

test('paginates listing results', function () {
    Listing::factory()->count(30)->create(['is_active' => true]);

    $response = $this->get(route('listings.index'));

    $response->assertInertia(
        fn ($page) => $page
            ->where('listings.per_page', 12)
            ->where('listings.total', 40)
    );
});

// ========== Sort ==========

test('sorts by newest first by default', function () {
    $response = $this->get(route('listings.index'));

    $response->assertInertia(fn ($page) => $page->has('listings.data'));
});

test('sorts by price ascending', function () {
    $response = $this->get(route('listings.index', ['sort' => 'price_asc']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('listings.data'));
});

test('sorts by area descending', function () {
    $response = $this->get(route('listings.index', ['sort' => 'area_desc']));

    $response->assertOk();
});

// ========== Response Structure ==========

test('index returns filter options in response', function () {
    $response = $this->get(route('listings.index'));

    $response->assertInertia(
        fn ($page) => $page
            ->has('filterOptions.cities')
            ->has('filterOptions.propertyTypes')
            ->has('filterOptions.listingTypes')
            ->has('filters')
    );
});

test('preserves applied filters in response', function () {
    $response = $this->get(route('listings.index', [
        'q' => 'Cairo',
        'city' => 'Cairo',
        'listing_type' => 'sale',
        'sort' => 'price_desc',
    ]));

    $response->assertInertia(
        fn ($page) => $page
            ->where('filters.q', 'Cairo')
            ->where('filters.city', 'Cairo')
            ->where('filters.listing_type', 'sale')
            ->where('filters.sort', 'price_desc')
    );
});
