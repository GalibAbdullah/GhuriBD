<?php

namespace App\Http\Controllers;

use App\Models\Resort;
use App\Models\TourPackage;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /**
     * The authenticated Traveler's wishlist, optionally filtered to only
     * resorts or only tour packages.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Wishlist::class);

        $user = $request->user();

        $type = in_array($request->query('type'), ['resorts', 'packages'], true)
            ? $request->query('type')
            : null;

        $resortWishlist = $type === 'packages'
            ? collect()
            : $user->wishlists()->whereNotNull('resort_id')->with('resort')->latest()->get();

        $packageWishlist = $type === 'resorts'
            ? collect()
            : $user->wishlists()->whereNotNull('tour_package_id')->with('tourPackage')->latest()->get();

        return view('wishlist.index', [
            'resortWishlist' => $resortWishlist,
            'packageWishlist' => $packageWishlist,
            'type' => $type,
        ]);
    }

    /**
     * Add or remove a resort from the authenticated Traveler's wishlist.
     */
    public function toggleResort(Request $request, Resort $resort): RedirectResponse
    {
        Gate::authorize('create', Wishlist::class);

        $user = $request->user();
        $existing = $user->wishlists()->where('resort_id', $resort->id)->first();

        if ($existing) {
            Gate::authorize('delete', $existing);
            $existing->delete();

            return back()->with('status', 'Removed from your wishlist.');
        }

        $user->wishlists()->create(['resort_id' => $resort->id]);

        return back()->with('status', 'Added to your wishlist.');
    }

    /**
     * Add or remove a tour package from the authenticated Traveler's wishlist.
     */
    public function togglePackage(Request $request, TourPackage $package): RedirectResponse
    {
        Gate::authorize('create', Wishlist::class);

        $user = $request->user();
        $existing = $user->wishlists()->where('tour_package_id', $package->id)->first();

        if ($existing) {
            Gate::authorize('delete', $existing);
            $existing->delete();

            return back()->with('status', 'Removed from your wishlist.');
        }

        $user->wishlists()->create(['tour_package_id' => $package->id]);

        return back()->with('status', 'Added to your wishlist.');
    }
}
