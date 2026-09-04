<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use App\Enums\ResortStatus;
use App\Enums\RoomStatus;
use App\Enums\TourPackageStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\Resort;
use App\Models\Room;
use App\Models\TourPackage;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingCreated;
use App\Notifications\NewBookingReceived;
use App\Payments\PaymentGateway;
use App\Weather\WeatherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * List bookings. Travelers see only their own; Travel Partners see
     * bookings for the resorts/packages they own; Admins see everything.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Booking::class);

        $user = $request->user();
        $isAdmin = $user->hasRole(UserRole::ADMIN->value);
        $isPartner = $user->hasRole(UserRole::TRAVEL_PARTNER->value);

        $status = in_array($request->query('status'), BookingStatus::values(), true)
            ? $request->query('status')
            : null;

        $query = match (true) {
            $isAdmin => Booking::query()->with(['user', 'resort', 'room', 'tourPackage', 'guideAvailability.guide']),
            $isPartner => Booking::query()->forPartner($user)->with(['user', 'resort', 'room', 'tourPackage', 'guideAvailability.guide']),
            default => $user->bookings()->with(['resort', 'room', 'tourPackage', 'guideAvailability.guide']),
        };

        if (! $isAdmin && ! $isPartner) {
            $scope = in_array($request->query('scope'), ['upcoming', 'history', 'all'], true)
                ? $request->query('scope')
                : 'upcoming';

            $query->when($scope === 'upcoming', fn ($query) => $query->upcoming())
                ->when($scope === 'history', fn ($query) => $query->history());
        }

        $type = null;

        if ($isPartner) {
            $type = in_array($request->query('type'), ['resort', 'package', 'guide'], true)
                ? $request->query('type')
                : null;

            // Scoped to bookings on THIS partner's own resort/package/guide slot,
            // not merely any booking that happens to have that FK set.
            $query->when($type === 'resort', fn ($query) => $query->whereHas('resort', fn ($query) => $query->where('user_id', $user->id)))
                ->when($type === 'package', fn ($query) => $query->whereHas('tourPackage', fn ($query) => $query->where('user_id', $user->id)))
                ->when($type === 'guide', fn ($query) => $query->whereHas('guideAvailability', fn ($query) => $query->where('user_id', $user->id)));
        }

        $bookings = $query
            ->when($status, fn ($query, $status) => $query->where('booking_status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($isAdmin) {
            return view('admin.bookings.index', [
                'bookings' => $bookings,
                'status' => $status,
                'statuses' => BookingStatus::values(),
            ]);
        }

        if ($isPartner) {
            return view('partner.bookings.index', [
                'bookings' => $bookings,
                'status' => $status,
                'statuses' => BookingStatus::values(),
                'type' => $type,
            ]);
        }

        return view('traveler.bookings.index', [
            'bookings' => $bookings,
            'scope' => $scope ?? 'upcoming',
            'status' => $status,
            'statuses' => BookingStatus::values(),
        ]);
    }

    /**
     * Resort Booking: select a room within a resort, dates, and guest count.
     */
    public function createResort(Resort $resort, Room $room, WeatherService $weather): View
    {
        abort_unless($room->resort_id === $resort->id, 404);

        Gate::authorize('create', Booking::class);

        return view('bookings.resort-create', [
            'resort' => $resort,
            'room' => $room,
            'forecast' => $resort->hasCoordinates() ? $weather->forecast((float) $resort->latitude, (float) $resort->longitude) : null,
        ]);
    }

    /**
     * Tour Package Booking: select a travel date and traveler count.
     */
    public function createPackage(TourPackage $package, WeatherService $weather): View
    {
        Gate::authorize('create', Booking::class);

        return view('bookings.package-create', [
            'package' => $package,
            'forecast' => $package->hasCoordinates() ? $weather->forecast((float) $package->latitude, (float) $package->longitude) : null,
        ]);
    }

    /**
     * Guide Booking: reserve seats in a Tour Guide's published time slot.
     */
    public function createGuide(GuideAvailability $availability): View
    {
        abort_unless($availability->isBookable(), 404);

        Gate::authorize('create', Booking::class);

        return view('bookings.guide-create', [
            'availability' => $availability->load('guide'),
        ]);
    }

    /**
     * Combined Booking: pick both a tour package and a resort/room in one
     * checkout. Optional query params pre-select a starting point.
     */
    public function createCombined(Request $request): View
    {
        Gate::authorize('create', Booking::class);

        $resorts = Resort::query()
            ->where('status', ResortStatus::ACTIVE->value)
            ->with(['rooms' => fn ($query) => $query->where('status', RoomStatus::AVAILABLE->value)])
            ->orderBy('name')
            ->get();

        $packages = TourPackage::query()
            ->where('status', TourPackageStatus::ACTIVE->value)
            ->orderBy('title')
            ->get();

        return view('bookings.combined-create', [
            'resorts' => $resorts,
            'packages' => $packages,
            'selectedResortId' => $request->integer('resort'),
            'selectedRoomId' => $request->integer('room'),
            'selectedPackageId' => $request->integer('package'),
        ]);
    }

    /**
     * Create a Resort, Tour Package, or Combined booking. The total is
     * always computed server-side from the current room/package prices —
     * never trusted from the request.
     */
    public function store(StoreBookingRequest $request, PaymentGateway $gateway): RedirectResponse
    {
        Gate::authorize('create', Booking::class);

        $data = $request->validated();

        $booking = DB::transaction(function () use ($data, $request) {
            $room = null;
            $resort = null;
            $package = null;
            $availability = null;
            $total = 0;

            if ($data['room_id'] ?? null) {
                // Lock the room's active bookings for this range so a concurrent
                // request can't slip past the availability check below.
                $overlapping = Booking::query()
                    ->overlappingForRoom($data['room_id'], $data['check_in_date'], $data['check_out_date'])
                    ->lockForUpdate()
                    ->count();

                $room = Room::findOrFail($data['room_id']);
                $resort = $room->resort;

                abort_if($overlapping >= $room->available_rooms, 422, 'This room was just booked out for the selected dates.');

                $nights = (int) Carbon::parse($data['check_in_date'])
                    ->diffInDays(Carbon::parse($data['check_out_date']));

                $total += (float) $room->price_per_night * $nights;
            }

            if ($data['tour_package_id'] ?? null) {
                $package = TourPackage::findOrFail($data['tour_package_id']);
                $total += (float) $package->price * (int) $data['guests'];
            }

            if ($data['guide_availability_id'] ?? null) {
                // Lock the slot row so a concurrent booking can't slip past the
                // capacity check below with a stale seat count.
                $availability = GuideAvailability::query()->lockForUpdate()->findOrFail($data['guide_availability_id']);

                abort_unless($availability->isBookable(), 422, 'This slot was just booked out.');
                abort_if((int) $data['guests'] > $availability->remainingCapacity(), 422, 'Not enough seats left in this slot.');

                $total += (float) $availability->price * (int) $data['guests'];
            }

            $booking = $request->user()->bookings()->create([
                'resort_id' => $resort?->id,
                'room_id' => $room?->id,
                'tour_package_id' => $package?->id,
                'guide_availability_id' => $availability?->id,
                'booking_type' => $data['booking_type'],
                'check_in_date' => $data['check_in_date'] ?? null,
                'check_out_date' => $data['check_out_date'] ?? null,
                'travel_date' => $data['travel_date'] ?? $availability?->available_date?->toDateString(),
                'guests' => $data['guests'],
                'total_amount' => $total,
                'booking_status' => BookingStatus::PENDING->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'special_request' => $data['special_request'] ?? null,
            ]);

            $availability?->reserveSeats((int) $data['guests']);

            return $booking;
        });

        $booking->load(['resort.user', 'tourPackage.user', 'guideAvailability.guide', 'user']);

        $booking->user->notify(new BookingCreated($booking));

        if ($booking->resort) {
            $booking->resort->user->notify(new NewBookingReceived($booking, 'resort'));
        }

        if ($booking->tourPackage) {
            $booking->tourPackage->user->notify(new NewBookingReceived($booking, 'tour package'));
        }

        if ($booking->guideAvailability) {
            $booking->guideAvailability->guide->notify(new NewBookingReceived($booking, 'guide session'));
        }

        $session = $gateway->initiate($booking);

        return redirect()
            ->to($session->redirectUrl)
            ->with('status', 'Booking placed. Complete payment to confirm your reservation.');
    }

    /**
     * Show a single booking. Accessible by its owning Traveler, by a Travel
     * Partner who owns the resort/package involved, and by Admins.
     */
    public function show(Request $request, Booking $booking, WeatherService $weather): View
    {
        Gate::authorize('view', $booking);

        $booking->load(['user', 'resort', 'room', 'tourPackage', 'guideAvailability.guide']);

        $location = $booking->resort ?? $booking->tourPackage;
        $forecast = $location?->hasCoordinates()
            ? $weather->forecast((float) $location->latitude, (float) $location->longitude)
            : null;

        if ($request->user()->hasRole(UserRole::TRAVELER->value)) {
            return view('bookings.show', ['booking' => $booking, 'audience' => 'traveler', 'forecast' => $forecast]);
        }

        if ($request->user()->hasRole(UserRole::TRAVEL_PARTNER->value)) {
            return view('bookings.show', ['booking' => $booking, 'audience' => 'partner', 'forecast' => $forecast]);
        }

        return view('bookings.show', ['booking' => $booking, 'audience' => 'admin', 'forecast' => $forecast]);
    }

    /**
     * Cancel a booking. Only the owning Traveler may cancel, and only while
     * the booking is still cancellable.
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $booking->load('guideAvailability');
        $booking->guideAvailability?->releaseSeats($booking->guests);

        $booking->update(['booking_status' => BookingStatus::CANCELLED->value]);
        $booking->load(['resort.user', 'tourPackage.user', 'guideAvailability.guide', 'user']);

        $booking->user->notify(new BookingCancelled($booking));

        if ($booking->resort) {
            $booking->resort->user->notify(new BookingCancelled($booking));
        }

        if ($booking->tourPackage) {
            $booking->tourPackage->user->notify(new BookingCancelled($booking));
        }

        if ($booking->guideAvailability) {
            $booking->guideAvailability->guide->notify(new BookingCancelled($booking));
        }

        return redirect()
            ->route('traveler.bookings.show', $booking)
            ->with('status', 'Booking cancelled.');
    }

    /**
     * Mark a confirmed booking as Completed. Only the Travel Partner who owns
     * the booked resort/package may do this — it's what unlocks the
     * traveler's ability to leave a review.
     */
    public function complete(Booking $booking): RedirectResponse
    {
        Gate::authorize('complete', $booking);

        $booking->update(['booking_status' => BookingStatus::COMPLETED->value]);

        return redirect()
            ->route('partner.bookings.show', $booking)
            ->with('status', 'Booking marked as completed.');
    }
}
