<?php

use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['is_admin' => false]);
    $this->listing = Listing::factory()->create(['is_active' => true]);
});

test('user can favorite a listing', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('listings.favorite', $this->listing));

    $response->assertOk();
    $response->assertJson(['success' => true, 'is_favorited' => true]);

    expect(Favorite::where('user_id', $this->user->id)->count())->toBe(1);
});

test('user can unfavorite a listing by toggling again', function () {
    $this->user->favorites()->attach($this->listing);

    $response = $this->actingAs($this->user)
        ->postJson(route('listings.favorite', $this->listing));

    $response->assertOk();
    $response->assertJson(['is_favorited' => false]);

    expect(Favorite::where('user_id', $this->user->id)->count())->toBe(0);
});

test('guest cannot favorite a listing', function () {
    $this->postJson(route('listings.favorite', $this->listing))->assertUnauthorized();
});

test('favoriting twice does not create duplicate rows', function () {
    $this->user->favorites()->attach($this->listing);

    // Toggle on (already on) — should not duplicate due to unique constraint logic.
    $this->user->favorites()->detach($this->listing);
    $this->user->favorites()->attach($this->listing);

    expect(Favorite::where('user_id', $this->user->id)->count())->toBe(1);
});

test('customer dashboard shows the users favorites', function () {
    $favorite = Listing::factory()->create(['is_active' => true, 'title' => 'My Favorite']);
    $this->user->favorites()->attach($favorite);

    $response = $this->actingAs($this->user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('favorites', 1)
        ->where('favoritesCount', 1)
        ->where('favorites.0.title', 'My Favorite')
    );
});

test('soft-deleting a listing keeps its favorites (restorable)', function () {
    $this->user->favorites()->attach($this->listing);

    $this->listing->delete();

    expect(Favorite::where('listing_id', $this->listing->id)->count())->toBe(1);
});

test('force-deleting a listing cascades its favorites', function () {
    $this->user->favorites()->attach($this->listing);

    $this->listing->forceDelete();

    expect(Favorite::where('listing_id', $this->listing->id)->count())->toBe(0);
});
