<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('customer can access dashboard', function () {
    $user = User::factory()->create([
        'is_admin' => false,
        'email_verified_at' => now(),
    ]);

    actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($assert) => $assert
            ->component('Dashboard')
        );
});

test('unauthenticated user cannot access dashboard', function () {
    get('/dashboard')->assertRedirect('/login');
});

test('unverified user is redirected to email verification', function () {
    $user = User::factory()->create([
        'is_admin' => false,
        'email_verified_at' => null,
    ]);

    actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/email/verify');
});
