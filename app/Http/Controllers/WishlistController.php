<?php

namespace App\Http\Controllers;

use App\Models\Laptop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function toggle(Request $request, Laptop $laptop): RedirectResponse
    {
        $wishlist = $request->user()->wishlists()->where('laptop_id', $laptop->id);

        if ($wishlist->exists()) {
            $wishlist->delete();
            return back()->with('success', 'Removed from favourites.');
        }

        abort_unless($laptop->status === 'available' && $laptop->approval_status === 'approved', 422, 'Only available laptops can be added to favourites.');

        $request->user()->wishlists()->firstOrCreate(['laptop_id' => $laptop->id]);

        return back()->with('success', 'Added to favourites.');
    }

    public function index(Request $request): View
    {
        $wishlists = $request->user()->wishlists()
            ->with([
                'laptop.images',
                'laptop.seller' => fn ($query) => $query->withCount('reviewsReceived')->withAvg('reviewsReceived', 'rating'),
            ])
            ->latest()
            ->paginate(12);

        return view('wishlist.index', compact('wishlists'));
    }
}
