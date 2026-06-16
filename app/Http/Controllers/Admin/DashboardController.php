<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingClick;
use App\Models\ListingView;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Listing::class);

        $totalListings = Listing::count();
        $activeListings = Listing::active()->count();
        $totalViews = ListingView::count();
        $totalClicks = ListingClick::count();

        $recentListings = Listing::latest()->take(5)->get();

        $mostViewed = Listing::active()
            ->withCount('views')
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        $viewsLast7Days = ListingView::query()
            ->inLastDays(7)
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalListings' => $totalListings,
                'activeListings' => $activeListings,
                'totalViews' => $totalViews,
                'totalClicks' => $totalClicks,
            ],
            'recentListings' => $recentListings,
            'mostViewed' => $mostViewed,
            'viewsLast7Days' => $viewsLast7Days,
        ]);
    }
}
