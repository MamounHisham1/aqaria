<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle a listing in the authenticated user's favorites.
     */
    public function toggle(Request $request, Listing $listing): JsonResponse
    {
        $user = $request->user();

        $isFavorited = $user->favorites()->where('listing_id', $listing->id)->exists();

        if ($isFavorited) {
            $user->favorites()->detach($listing);
            $isFavorited = false;
        } else {
            $user->favorites()->attach($listing);
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
        ]);
    }
}
