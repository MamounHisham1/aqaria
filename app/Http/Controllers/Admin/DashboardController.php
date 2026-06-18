<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
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
        $totalLeads = Lead::count();
        $closedLeads = Lead::where('status', 'closed')->count();

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

        // CRM funnel + responsiveness metrics.
        $funnel = [
            'views' => $totalViews,
            'clicks' => $totalClicks,
            'leads' => $totalLeads,
            'closed' => $closedLeads,
        ];

        $avgTimeToContact = $this->averageTimeToContact();

        $recentLeads = Lead::query()
            ->with('listing:id,title,city')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalListings' => $totalListings,
                'activeListings' => $activeListings,
                'totalViews' => $totalViews,
                'totalClicks' => $totalClicks,
                'totalLeads' => $totalLeads,
                'closedLeads' => $closedLeads,
            ],
            'recentListings' => $recentListings,
            'mostViewed' => $mostViewed,
            'viewsLast7Days' => $viewsLast7Days,
            'funnel' => $funnel,
            'avgTimeToContactHours' => $avgTimeToContact,
            'recentLeads' => $recentLeads,
        ]);
    }

    /**
     * Average hours between a lead being submitted and first contacted.
     * Computed in PHP (not raw SQL) so it works across SQLite/MySQL/Postgres.
     * Returns null when no contacted leads exist.
     */
    private function averageTimeToContact(): ?float
    {
        $leads = Lead::query()
            ->whereNotNull('contacted_at')
            ->whereNotNull('created_at')
            ->get(['created_at', 'contacted_at']);

        if ($leads->isEmpty()) {
            return null;
        }

        $totalSeconds = $leads->sum(function (Lead $lead) {
            return $lead->created_at->diffInSeconds($lead->contacted_at);
        });

        return round($totalSeconds / $leads->count() / 3600, 1);
    }
}
