<?php

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ========== Admin Dashboard ==========

test('admin dashboard renders with correct props', function () {
    Listing::factory()->count(3)->create(['is_active' => true]);
    Listing::factory()->count(2)->create(['is_active' => false]);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Dashboard')
        ->has('stats')
        ->where('stats.totalListings', 5)
        ->where('stats.activeListings', 3)
        ->has('recentListings')
        ->has('mostViewed')
        ->has('viewsLast7Days')
    );
});

// ========== Admin Listings ==========

test('admin listings index renders with paginated data', function () {
    Listing::factory()->count(5)->create();

    $response = $this->get(route('admin.listings.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Listings/Index')
        ->has('listings.data', 5)
        ->has('listings.total')
    );
});

test('admin listings create page renders', function () {
    $response = $this->get(route('admin.listings.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Listings/Create')
    );
});

test('admin listings edit page renders with listing data', function () {
    $listing = Listing::factory()->create(['title' => 'Test Listing']);

    $response = $this->get(route('admin.listings.edit', $listing));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Listings/Edit')
        ->where('listing.title', 'Test Listing')
    );
});

// ========== Auth Required ==========

test('admin pages require authentication', function () {
    auth()->logout();

    $this->get(route('admin.dashboard'))->assertRedirect();
    $this->get(route('admin.listings.index'))->assertRedirect();
    $this->get(route('admin.analytics'))->assertRedirect();
});
