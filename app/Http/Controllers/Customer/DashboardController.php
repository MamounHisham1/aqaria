<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $favorites = $user->favorites()
            ->active()
            ->withCount('views')
            ->latest('favorites.created_at')
            ->take(6)
            ->get();

        $savedSearches = $user->savedSearches()
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Dashboard', [
            'favorites' => $favorites,
            'savedSearches' => $savedSearches,
            'favoritesCount' => $user->favorites()->count(),
        ]);
    }
}
