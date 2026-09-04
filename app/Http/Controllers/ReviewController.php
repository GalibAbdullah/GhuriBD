<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Booking;
use App\Models\Review;
use App\Notifications\NewReviewReceived;
use App\Notifications\ReviewSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Show the form to review a completed booking.
     */
    public function create(Booking $booking): View
    {
        Gate::authorize('create', [Review::class, $booking]);

        return view('reviews.create', [
            'booking' => $booking->load(['resort', 'tourPackage']),
        ]);
    }

    /**
     * Submit a review for a completed booking. Notifies the traveler
     * (confirmation) and the resort/package owner (new review).
     */
    public function store(ReviewRequest $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('create', [Review::class, $booking]);

        $review = Review::create([
            ...$request->validated(),
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'resort_id' => $booking->resort_id,
            'tour_package_id' => $booking->tour_package_id,
        ]);

        $review->load(['user', 'resort.user', 'tourPackage.user']);

        $review->user->notify(new ReviewSubmitted($review));

        $owner = $review->resort?->user ?? $review->tourPackage?->user;
        $owner?->notify(new NewReviewReceived($review));

        $redirectRoute = $review->resort ? 'traveler.resorts.show' : 'traveler.packages.show';
        $redirectModel = $review->resort ?? $review->tourPackage;

        return redirect()
            ->route($redirectRoute, $redirectModel)
            ->with('status', 'Thanks! Your review has been submitted.');
    }

    /**
     * Reviews left on this Travel Partner's own resorts/packages.
     */
    public function partnerIndex(Request $request): View
    {
        Gate::authorize('viewAny', Review::class);

        $reviews = Review::query()
            ->forPartner($request->user())
            ->with(['user', 'resort', 'tourPackage'])
            ->latest()
            ->paginate(15);

        return view('partner.reviews.index', ['reviews' => $reviews]);
    }

    /**
     * Every review, for Admin moderation.
     */
    public function adminIndex(): View
    {
        Gate::authorize('viewAny', Review::class);

        $reviews = Review::query()
            ->with(['user', 'resort', 'tourPackage'])
            ->latest()
            ->paginate(15);

        return view('admin.reviews.index', ['reviews' => $reviews]);
    }

    /**
     * A Travel Partner replies to a review left on their own resort/package.
     * The traveler's rating and text are never editable here.
     */
    public function reply(Request $request, Review $review): RedirectResponse
    {
        Gate::authorize('reply', $review);

        $data = $request->validate([
            'partner_reply' => ['required', 'string', 'max:2000'],
        ]);

        $review->update($data);

        return back()->with('status', 'Reply posted.');
    }

    /**
     * Admin deletes an inappropriate review.
     */
    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);

        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
