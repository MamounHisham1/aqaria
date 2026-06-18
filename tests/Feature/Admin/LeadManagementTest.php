<?php

use App\Models\Lead;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($this->admin);
});

// ========== Authorization ==========

test('guest cannot access admin leads index', function () {
    auth()->logout();

    $this->get(route('admin.leads.index'))->assertRedirect(route('login'));
});

test('non-admin cannot access admin leads index', function () {
    auth()->logout();
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    $this->get(route('admin.leads.index'))->assertForbidden();
});

// ========== Index ==========

test('admin leads index lists leads with status counts', function () {
    $listing = Listing::factory()->create();
    Lead::factory()->count(2)->statusNew()->create(['listing_id' => $listing->id]);
    Lead::factory()->create(['listing_id' => $listing->id, 'status' => 'closed']);

    $response = $this->get(route('admin.leads.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Leads/Index')
        ->has('leads.data', 3)
        ->has('statuses')
        ->where('statusCounts.new', 2)
        ->where('statusCounts.closed', 1)
    );
});

test('admin leads index filters by status', function () {
    $listing = Listing::factory()->create();
    Lead::factory()->statusNew()->create(['listing_id' => $listing->id]);
    Lead::factory()->create(['listing_id' => $listing->id, 'status' => 'closed']);

    $response = $this->get(route('admin.leads.index', ['status' => 'new']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('leads.data', 1)
        ->where('currentStatus', 'new')
    );
});

// ========== Show ==========

test('admin can view a single lead', function () {
    $lead = Lead::factory()->create(['name' => 'Sara Saleh']);

    $response = $this->get(route('admin.leads.show', $lead));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Leads/Show')
        ->where('lead.name', 'Sara Saleh')
        ->has('statuses')
    );
});

// ========== Update ==========

test('admin can update lead status and stamp contacted timestamp', function () {
    $lead = Lead::factory()->statusNew()->create();

    $response = $this->patch(route('admin.leads.update', $lead), [
        'status' => 'contacted',
    ]);

    $response->assertRedirect(route('admin.leads.show', $lead));

    $lead->refresh();
    expect($lead->status)->toBe('contacted')
        ->and($lead->contacted_at)->not->toBeNull();
});

test('admin can set lead to closed and stamp closed timestamp', function () {
    $lead = Lead::factory()->statusNew()->create();

    $this->patch(route('admin.leads.update', $lead), [
        'status' => 'closed',
    ]);

    $lead->refresh();
    expect($lead->status)->toBe('closed')
        ->and($lead->closed_at)->not->toBeNull();
});

test('admin can save admin notes', function () {
    $lead = Lead::factory()->create();

    $this->patch(route('admin.leads.update', $lead), [
        'admin_notes' => 'Called twice, no answer.',
    ]);

    expect($lead->fresh()->admin_notes)->toBe('Called twice, no answer.');
});

test('update validates status against allowed list', function () {
    $lead = Lead::factory()->create();

    $response = $this->patch(route('admin.leads.update', $lead), [
        'status' => 'invalid-status',
    ]);

    $response->assertSessionHasErrors(['status']);
});

test('non-admin cannot update a lead', function () {
    auth()->logout();
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    $lead = Lead::factory()->create();

    $this->patch(route('admin.leads.update', $lead), ['status' => 'closed'])->assertForbidden();
});
