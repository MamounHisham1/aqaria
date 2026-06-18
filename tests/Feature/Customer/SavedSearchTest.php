<?php

use App\Models\Listing;
use App\Models\SavedSearch;
use App\Models\User;
use App\Notifications\NewMatchingListings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['is_admin' => false]);
});

// ========== Storing ==========

test('user can save a search with filters', function () {
    $response = $this->actingAs($this->user)->post(route('saved-searches.store'), [
        'name' => '3-bed in Cairo',
        'notify' => true,
        'filters' => [
            'city' => 'Cairo',
            'bedrooms' => 3,
            'listing_type' => 'sale',
        ],
    ]);

    $response->assertRedirect(route('dashboard'));

    $search = SavedSearch::first();
    expect($search)->not->toBeNull()
        ->and($search->name)->toBe('3-bed in Cairo')
        ->and($search->user_id)->toBe($this->user->id)
        ->and($search->notify)->toBeTrue()
        ->and($search->filters['city'])->toBe('Cairo')
        ->and($search->filters['bedrooms'])->toBe(3);
});

test('save search validates name is required', function () {
    $this->actingAs($this->user)
        ->post(route('saved-searches.store'), ['name' => '', 'filters' => []])
        ->assertSessionHasErrors(['name']);
});

test('save search rejects invalid filter values', function () {
    $this->actingAs($this->user)
        ->post(route('saved-searches.store'), [
            'name' => 'bad',
            'filters' => ['listing_type' => 'auction'],
        ])
        ->assertSessionHasErrors(['filters.listing_type']);
});

test('user can delete their own saved search', function () {
    $search = SavedSearch::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->delete(route('saved-searches.destroy', $search))
        ->assertRedirect(route('dashboard'));

    expect(SavedSearch::find($search->id))->toBeNull();
});

test('user cannot delete another users saved search', function () {
    $otherUser = User::factory()->create();
    $search = SavedSearch::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user)
        ->delete(route('saved-searches.destroy', $search))
        ->assertForbidden();
});

// ========== Matching (command) ==========

test('saved search matches listings by stored filters', function () {
    $search = SavedSearch::factory()->create([
        'user_id' => $this->user->id,
        'filters' => ['city' => 'Cairo', 'bedrooms' => 3],
    ]);

    Listing::factory()->create(['is_active' => true, 'city' => 'Cairo', 'bedrooms' => 3]);
    Listing::factory()->create(['is_active' => true, 'city' => 'Cairo', 'bedrooms' => 2]);
    Listing::factory()->create(['is_active' => true, 'city' => 'Giza', 'bedrooms' => 3]);

    expect($search->matchingListings()->count())->toBe(1);
});

test('send-saved-search-alerts command emails users about new matches', function () {
    Notification::fake();

    $search = SavedSearch::factory()->create([
        'user_id' => $this->user->id,
        'notify' => true,
        'last_notified_at' => now()->subDay(),
        'filters' => ['city' => 'Cairo'],
    ]);

    // One new listing created after last_notified_at.
    Listing::factory()->create([
        'is_active' => true,
        'city' => 'Cairo',
        'created_at' => now()->subHour(),
    ]);

    $this->artisan('app:send-saved-search-alerts')->assertSuccessful();

    Notification::assertSentTo($this->user, NewMatchingListings::class);
    expect($search->fresh()->last_notified_at)->not->toBeNull();
});

test('command does not email when there are no new matches', function () {
    Notification::fake();

    SavedSearch::factory()->create([
        'user_id' => $this->user->id,
        'notify' => true,
        'filters' => ['city' => 'Nowhere'],
    ]);

    $this->artisan('app:send-saved-search-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});

test('command only processes searches with notify enabled', function () {
    Notification::fake();

    SavedSearch::factory()->create([
        'user_id' => $this->user->id,
        'notify' => false,
        'filters' => ['city' => 'Cairo'],
    ]);

    Listing::factory()->create(['is_active' => true, 'city' => 'Cairo']);

    $this->artisan('app:send-saved-search-alerts')->assertSuccessful();

    Notification::assertNothingSent();
});
