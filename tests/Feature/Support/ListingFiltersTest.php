<?php

use App\Models\Listing;
use App\Support\ListingFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('keys returns the canonical filter key list', function () {
    expect(ListingFilters::keys())->toBe([
        'q', 'city', 'listing_type', 'property_type',
        'min_price', 'max_price', 'min_area', 'max_area',
        'bedrooms', 'bathrooms',
    ]);
});

test('rules covers every key in keys()', function () {
    foreach (ListingFilters::keys() as $key) {
        expect(ListingFilters::rules())->toHaveKey($key);
    }
});

test('normalize drops empty and unknown keys', function () {
    $filters = ListingFilters::normalize([
        'city' => 'Cairo',
        'bedrooms' => 3,
        'empty' => '',
        'null' => null,
        'unknown' => 'x',
    ]);

    expect($filters->all())->toBe(['city' => 'Cairo', 'bedrooms' => 3]);
});

test('apply translates each filter to the right scope', function () {
    Listing::factory()->create(['is_active' => true, 'city' => 'Cairo', 'bedrooms' => 3, 'listing_type' => 'sale']);
    Listing::factory()->create(['is_active' => true, 'city' => 'Cairo', 'bedrooms' => 2, 'listing_type' => 'sale']);
    Listing::factory()->create(['is_active' => true, 'city' => 'Giza', 'bedrooms' => 3, 'listing_type' => 'sale']);
    Listing::factory()->create(['is_active' => true, 'city' => 'Cairo', 'bedrooms' => 3, 'listing_type' => 'rent']);

    $results = ListingFilters::apply(Listing::query(), [
        'city' => 'Cairo',
        'bedrooms' => 3,
        'listing_type' => 'sale',
    ])->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->city)->toBe('Cairo')
        ->and($results->first()->bedrooms)->toBe(3)
        ->and($results->first()->listing_type)->toBe('sale');
});

test('apply is a no-op for an empty filter set', function () {
    Listing::factory()->count(3)->create(['is_active' => true]);

    $count = ListingFilters::apply(Listing::query(), [])->count();

    expect($count)->toBe(3);
});
