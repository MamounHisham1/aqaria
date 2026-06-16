<?php

use App\Models\Listing;
use App\Models\User;

it('allows admin to view any listing', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    expect($admin->can('viewAny', Listing::class))->toBeTrue();
});

it('prevents non-admin from viewing any listing', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->can('viewAny', Listing::class))->toBeFalse();
});

it('allows anyone to view active listing', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $listing = Listing::factory()->create(['is_active' => true]);

    expect($user->can('view', $listing))->toBeTrue();
});

it('prevents non-admin from viewing inactive listing', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $listing = Listing::factory()->create(['is_active' => false]);

    expect($user->can('view', $listing))->toBeFalse();
});

it('allows admin to view inactive listing', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $listing = Listing::factory()->create(['is_active' => false]);

    expect($admin->can('view', $listing))->toBeTrue();
});

it('allows admin to create listing', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    expect($admin->can('create', Listing::class))->toBeTrue();
});

it('prevents non-admin from creating listing', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->can('create', Listing::class))->toBeFalse();
});

it('allows admin to update listing', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $listing = Listing::factory()->create();

    expect($admin->can('update', $listing))->toBeTrue();
});

it('prevents non-admin from updating listing', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $listing = Listing::factory()->create();

    expect($user->can('update', $listing))->toBeFalse();
});

it('allows admin to delete listing', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $listing = Listing::factory()->create();

    expect($admin->can('delete', $listing))->toBeTrue();
});

it('prevents non-admin from deleting listing', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $listing = Listing::factory()->create();

    expect($user->can('delete', $listing))->toBeFalse();
});

it('allows admin to toggle active status of listing', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $listing = Listing::factory()->create();

    expect($admin->can('toggleActive', $listing))->toBeTrue();
});

it('prevents non-admin from toggling active status of listing', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $listing = Listing::factory()->create();

    expect($user->can('toggleActive', $listing))->toBeFalse();
});
