<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingClick;
use App\Models\ListingView;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard.
     */
    public function index(Request $request): \Inertia\Response
    {
        $days = $request->input('days', 30);

        $viewsOverTime = ListingView::query()
            ->inLastDays($days)
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $clicksByType = ListingClick::query()
            ->inLastDays($days)
            ->selectRaw('click_type, COUNT(*) as count')
            ->groupBy('click_type')
            ->get();

        $topListings = Listing::active()
            ->withCount('views')
            ->withCount('clicks')
            ->orderByDesc('views_count')
            ->take(10)
            ->get();

        $totalViews = ListingView::query()->inLastDays($days)->count();
        $totalClicks = ListingClick::query()->inLastDays($days)->count();

        return Inertia::render('Admin/Analytics', [
            'viewsOverTime' => $viewsOverTime,
            'clicksByType' => $clicksByType,
            'topListings' => $topListings,
            'totalViews' => $totalViews,
            'totalClicks' => $totalClicks,
            'selectedDays' => $days,
        ]);
    }
}
