<?php

use App\Models\User;

it('allows admin to view any user', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    expect($admin->can('viewAny', User::class))->toBeTrue();
});

it('prevents non-admin from viewing any user', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->can('viewAny', User::class))->toBeFalse();
});

it('allows admin to view any specific user', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create(['is_admin' => false]);

    expect($admin->can('view', $otherUser))->toBeTrue();
});

it('allows user to view themselves', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->can('view', $user))->toBeTrue();
});

it('prevents user from viewing other users', function () {
    $user1 = User::factory()->create(['is_admin' => false]);
    $user2 = User::factory()->create(['is_admin' => false]);

    expect($user1->can('view', $user2))->toBeFalse();
});

it('allows admin to create user', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    expect($admin->can('create', User::class))->toBeTrue();
});

it('prevents non-admin from creating user', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->can('create', User::class))->toBeFalse();
});

it('allows admin to update any user', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create(['is_admin' => false]);

    expect($admin->can('update', $otherUser))->toBeTrue();
});

it('allows user to update themselves', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->can('update', $user))->toBeTrue();
});

it('prevents user from updating other users', function () {
    $user1 = User::factory()->create(['is_admin' => false]);
    $user2 = User::factory()->create(['is_admin' => false]);

    expect($user1->can('update', $user2))->toBeFalse();
});

it('allows admin to delete other users', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create(['is_admin' => false]);

    expect($admin->can('delete', $otherUser))->toBeTrue();
});

it('prevents admin from deleting themselves', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    expect($admin->can('delete', $admin))->toBeFalse();
});

it('prevents user from deleting themselves', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->can('delete', $user))->toBeFalse();
});

it('prevents user from deleting other users', function () {
    $user1 = User::factory()->create(['is_admin' => false]);
    $user2 = User::factory()->create(['is_admin' => false]);

    expect($user1->can('delete', $user2))->toBeFalse();
});
