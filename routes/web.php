<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\SavedSearchController;
use App\Models\Listing;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ========== Public Routes ==========

Route::get('/', function () {
    $featuredListings = Listing::active()->featured()->latest()->take(6)->get();
    $totalListings = Listing::active()->count();
    $cities = Listing::distinct()->pluck('city')->sort()->values();

    return Inertia::render('Welcome', [
        'featuredListings' => $featuredListings,
        'totalListings' => $totalListings,
        'cities' => $cities,
    ]);
})->name('home');

Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
Route::post('/listings/{listing}/click', [ListingController::class, 'recordClick'])->name('listings.click');
Route::post('/listings/{listing}/leads', [LeadController::class, 'store'])->name('listings.leads.store');

// ========== Customer Routes ==========

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');

    Route::post('/listings/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('listings.favorite');

    Route::post('/saved-searches', [SavedSearchController::class, 'store'])->name('saved-searches.store');
    Route::delete('/saved-searches/{savedSearch}', [SavedSearchController::class, 'destroy'])->name('saved-searches.destroy');
});

// ========== Admin Routes ==========

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/listings', [AdminListingController::class, 'index'])->name('listings.index');
    Route::get('/listings/create', [AdminListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [AdminListingController::class, 'store'])->name('listings.store');
    Route::get('/listings/{listing}/edit', [AdminListingController::class, 'edit'])->name('listings.edit');
    Route::put('/listings/{listing}', [AdminListingController::class, 'update'])->name('listings.update');
    Route::delete('/listings/{listing}', [AdminListingController::class, 'destroy'])->name('listings.destroy');
    Route::patch('/listings/{listing}/toggle', [AdminListingController::class, 'toggleActive'])->name('listings.toggle');

    Route::get('/leads', [AdminLeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [AdminLeadController::class, 'show'])->name('leads.show');
    Route::patch('/leads/{lead}', [AdminLeadController::class, 'update'])->name('leads.update');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
});

require __DIR__.'/settings.php';
