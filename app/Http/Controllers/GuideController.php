<?php

namespace App\Http\Controllers;

use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\ProviderVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuideController extends Controller
{
    /**
     * Browse verified Tour Guides — open to any authenticated role, since a
     * Travel Partner (e.g. a resort owner building an itinerary) may want to
     * find a guide just as much as a Traveler does.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $guides = User::query()
            ->role(UserRole::TRAVEL_PARTNER->value)
            ->whereHas('providerVerifications', function ($query) use ($search): void {
                $query->where('status', VerificationStatus::APPROVED->value)
                    ->where('provider_type', ProviderType::TOUR_GUIDE->value)
                    ->when($search !== '', function ($query) use ($search): void {
                        $query->where(function ($query) use ($search): void {
                            $query->where('provider_name', 'like', "%{$search}%")
                                ->orWhere('business_address', 'like', "%{$search}%");
                        });
                    });
            })
            ->with(['providerVerifications' => function ($query): void {
                $query->where('status', VerificationStatus::APPROVED->value)
                    ->where('provider_type', ProviderType::TOUR_GUIDE->value);
            }])
            ->withCount(['guideAvailabilities as upcoming_slots_count' => function ($query): void {
                $query->bookable();
            }])
            ->orderByDesc('upcoming_slots_count')
            ->paginate(12)
            ->withQueryString();

        return view('guides.index', [
            'guides' => $guides,
            'search' => $search,
        ]);
    }

    /**
     * A guide's public profile: their verified details and every upcoming,
     * bookable slot they currently have listed.
     */
    public function show(User $guide): View
    {
        abort_unless($guide->isVerifiedTourGuide(), 404);

        $verification = ProviderVerification::query()
            ->where('user_id', $guide->id)
            ->where('status', VerificationStatus::APPROVED->value)
            ->where('provider_type', ProviderType::TOUR_GUIDE->value)
            ->latest()
            ->firstOrFail();

        $availabilities = $guide->guideAvailabilities()
            ->bookable()
            ->orderBy('available_date')
            ->orderBy('start_time')
            ->get();

        return view('guides.show', [
            'guide' => $guide,
            'verification' => $verification,
            'availabilities' => $availabilities,
        ]);
    }
}
