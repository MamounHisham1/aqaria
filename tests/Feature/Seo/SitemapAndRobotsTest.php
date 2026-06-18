<?php

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========== robots.txt ==========

test('robots txt is served and disallows admin paths', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('text/plain');
    expect($response->content())
        ->toContain('User-agent: *')
        ->toContain('Disallow: /admin')
        ->toContain('Sitemap:');
});

// ========== sitemap.xml ==========

test('sitemap lists home and listings index', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('text/xml');
    expect($response->content())
        ->toContain('<loc>'.url('/'))
        ->toContain(route('listings.index'));
});

test('sitemap includes all active listings', function () {
    Listing::factory()->count(3)->create(['is_active' => true]);
    $inactive = Listing::factory()->create(['is_active' => false]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();

    $active = Listing::active()->get();
    foreach ($active as $listing) {
        expect($response->content())->toContain(route('listings.show', $listing));
    }

    // Inactive listings must NOT appear in the sitemap.
    expect($response->content())->not->toContain(route('listings.show', $inactive));
});

// ========== Listing show SEO props ==========

test('listing show page passes SEO and structured data', function () {
    $listing = Listing::factory()->create([
        'is_active' => true,
        'title' => 'Luxury Villa',
        'price' => 5000000,
        'latitude' => '30.0444',
        'longitude' => '31.2357',
    ]);

    $response = $this->get(route('listings.show', $listing));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('seo')
        ->where('seo.title', 'Luxury Villa — 5,000,000 EGP')
        ->has('seo.schema')
        ->where('seo.schema.@type', 'Product')
        ->where('seo.schema.offers.price', 5000000)
        ->where('seo.schema.offers.priceCurrency', 'EGP')
        ->where('seo.schema.geo.latitude', 30.0444)
    );
});
