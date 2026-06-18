<?php

use App\Models\Lead;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\NewLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->listing = Listing::factory()->create(['is_active' => true]);
});

// ========== Storing leads ==========

test('guest can submit a lead for an active listing', function () {
    Notification::fake();

    $response = $this->postJson(route('listings.leads.store', $this->listing), [
        'name' => 'Ahmed Ali',
        'phone' => '01012345678',
        'email' => 'ahmed@example.com',
        'message' => 'I am interested in this property.',
    ]);

    $response->assertRedirect(route('listings.show', $this->listing));

    $lead = Lead::first();
    expect($lead)->not->toBeNull()
        ->and($lead->name)->toBe('Ahmed Ali')
        ->and($lead->phone)->toBe('01012345678')
        ->and($lead->listing_id)->toBe($this->listing->id)
        ->and($lead->user_id)->toBeNull()
        ->and($lead->status)->toBe('new');
});

test('logged in user is attached to the lead', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('listings.leads.store', $this->listing), [
        'name' => $user->name,
        'phone' => '01012345678',
    ]);

    $response->assertRedirect(route('listings.show', $this->listing));

    expect(Lead::first()->user_id)->toBe($user->id);
});

test('submitting a lead notifies admins by mail', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_admin' => true]);
    User::factory()->create(['is_admin' => false]);

    $this->postJson(route('listings.leads.store', $this->listing), [
        'name' => 'Ahmed',
        'phone' => '01012345678',
    ]);

    Notification::assertSentTo($admin, NewLead::class);
});

test('lead submission dispatches notification job even without admins', function () {
    Notification::fake();

    // No admin users exist — should still succeed without errors.
    $response = $this->postJson(route('listings.leads.store', $this->listing), [
        'name' => 'Ahmed',
        'phone' => '01012345678',
    ]);

    $response->assertRedirect();
    expect(Lead::count())->toBe(1);
    Notification::assertNothingSent();
});

test('lead cannot be submitted for an inactive listing', function () {
    Notification::fake();

    $inactive = Listing::factory()->create(['is_active' => false]);

    $response = $this->postJson(route('listings.leads.store', $inactive), [
        'name' => 'Ahmed',
        'phone' => '01012345678',
    ]);

    $response->assertNotFound();
    expect(Lead::count())->toBe(0);
});

test('lead validates required fields', function () {
    $response = $this->postJson(route('listings.leads.store', $this->listing), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'phone']);
});

test('lead validates email format', function () {
    $response = $this->postJson(route('listings.leads.store', $this->listing), [
        'name' => 'Ahmed',
        'phone' => '01012345678',
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});
