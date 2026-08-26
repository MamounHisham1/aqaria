<?php

use App\Models\Listing;
use App\Models\ListingView;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->activeSale = Listing::factory()->create([
        'is_active' => true,
        'is_featured' => false,
        'listing_type' => 'sale',
        'property_type' => 'apartment',
        'city' => 'Cairo',
        'district' => 'Maadi',
        'price' => 1000000,
        'area_sqm' => 150,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'images' => ['https://example.com/img1.jpg', 'https://example.com/img2.jpg'],
    ]);

    $this->featuredRent = Listing::factory()->create([
        'is_active' => true,
        'is_featured' => true,
        'listing_type' => 'rent',
        'property_type' => 'villa',
        'city' => 'Alexandria',
        'district' => 'Smouha',
        'price' => 25000,
        'area_sqm' => 400,
        'bedrooms' => 5,
        'bathrooms' => 4,
    ]);

    $this->inactiveListing = Listing::factory()->create([
        'is_active' => false,
        'is_featured' => true,
        'listing_type' => 'sale',
        'property_type' => 'commercial',
        'city' => 'Cairo',
        'district' => 'Zamalek',
        'price' => 5000000,
        'area_sqm' => 200,
        'bedrooms' => 0,
        'bathrooms' => 1,
    ]);

    $this->luxorListing = Listing::factory()->create([
        'is_active' => true,
        'listing_type' => 'sale',
        'property_type' => 'apartment',
        'city' => 'Luxor',
        'district' => 'Karnak',
        'price' => 750000,
        'area_sqm' => 120,
        'bedrooms' => 4,
        'bathrooms' => 3,
    ]);
});

// ========== Scopes ==========

test('active scope returns only active listings', function () {
    $results = Listing::active()->get();

    expect($results)->toHaveCount(3);
    expect($results->pluck('id')->toArray())->not->toContain($this->inactiveListing->id);
});

test('featured scope returns active and featured listings', function () {
    $results = Listing::featured()->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($this->featuredRent->id);
    expect($results->pluck('id')->toArray())->not->toContain($this->inactiveListing->id);
});

test('byListingType scope filters correctly', function () {
    $sales = Listing::byListingType('sale')->get();
    $rents = Listing::byListingType('rent')->get();

    expect($sales)->toHaveCount(3);
    expect($rents)->toHaveCount(1);
    expect($rents->first()->id)->toBe($this->featuredRent->id);
});

test('byPropertyType scope filters correctly', function () {
    $apartments = Listing::byPropertyType('apartment')->get();
    $villas = Listing::byPropertyType('villa')->get();

    expect($apartments)->toHaveCount(2);
    expect($villas)->toHaveCount(1);
});

test('inCity scope performs like search', function () {
    $cairoListings = Listing::inCity('Cairo')->get();
    $luxorListings = Listing::inCity('Luxor')->get();

    expect($cairoListings)->toHaveCount(2);
    expect($luxorListings)->toHaveCount(1);
});

test('search scope searches title description city and district', function () {
    $results = Listing::search('Luxor')->get();
    expect($results)->toHaveCount(1);

    $results = Listing::search('Maadi')->get();
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($this->activeSale->id);
});

test('priceBetween scope filters by min and max', function () {
    $results = Listing::priceBetween(500000, 2000000)->get();
    expect($results)->toHaveCount(2);
    expect($results->pluck('id')->toArray())
        ->toContain($this->activeSale->id)
        ->toContain($this->luxorListing->id);

    $results = Listing::priceBetween(30000, null)->get();
    expect($results)->toHaveCount(3);
});

test('areaBetween scope filters by min and max area', function () {
    $results = Listing::areaBetween(100, 300)->get();
    expect($results)->toHaveCount(3);
});

test('withBedrooms scope filters by minimum bedrooms', function () {
    $results = Listing::withBedrooms(4)->get();
    expect($results)->toHaveCount(2);
});

test('withBathrooms scope filters by minimum bathrooms', function () {
    $results = Listing::withBathrooms(3)->get();
    expect($results)->toHaveCount(2);
});

// ========== Accessors ==========

test('formattedPrice returns EGP formatted string', function () {
    expect($this->activeSale->formatted_price)->toBe('1,000,000 EGP');
    expect($this->featuredRent->formatted_price)->toBe('25,000 EGP');
});

test('primaryImage returns first image or null', function () {
    expect($this->activeSale->primary_image)->toBe('https://example.com/img1.jpg');

    $noImage = Listing::factory()->create(['images' => []]);
    expect($noImage->primary_image)->toBeNull();
});

test('viewsCount returns count of related views', function () {
    ListingView::factory()->count(5)->create(['listing_id' => $this->activeSale->id]);
    ListingView::factory()->count(2)->create(['listing_id' => $this->featuredRent->id]);

    expect($this->activeSale->views_count)->toBe(5);
    expect($this->featuredRent->views_count)->toBe(2);
    expect($this->luxorListing->views_count)->toBe(0);
});

test('clicksCount returns count of related clicks', function () {
    \App\Models\ListingClick::factory()->count(3)->create(['listing_id' => $this->activeSale->id]);

    expect($this->activeSale->clicks_count)->toBe(3);
    expect($this->featuredRent->clicks_count)->toBe(0);
});

// ========== Relationships ==========

test('listing has many views relationship', function () {
    ListingView::factory()->count(3)->create(['listing_id' => $this->activeSale->id]);

    expect($this->activeSale->views)->toHaveCount(3);
    expect($this->activeSale->views->first())->toBeInstanceOf(ListingView::class);
});

test('listing has many clicks relationship', function () {
    \App\Models\ListingClick::factory()->count(2)->create(['listing_id' => $this->activeSale->id]);

    expect($this->activeSale->clicks)->toHaveCount(2);
    expect($this->activeSale->clicks->first())->toBeInstanceOf(\App\Models\ListingClick::class);
});

// ========== Casts ==========

test('casts attributes correctly', function () {
    $listing = $this->activeSale->fresh();

    expect($listing->is_active)->toBeBool();
    expect($listing->is_featured)->toBeBool();
    expect($listing->images)->toBeArray();
    expect($listing->amenities)->toBeArray();
    expect($listing->bedrooms)->toBeInt();
    expect($listing->bathrooms)->toBeInt();
    expect($listing->area_sqm)->toBeInt();
});
