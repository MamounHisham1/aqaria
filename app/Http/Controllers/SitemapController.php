<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Render an XML sitemap of the home page and all active listings.
     */
    public function index(): Response
    {
        $listings = Listing::active()
            ->latest('updated_at')
            ->get(['id', 'updated_at']);

        return response()
            ->view('sitemap', ['listings' => $listings])
            ->header('Content-Type', 'text/xml');
    }
}
