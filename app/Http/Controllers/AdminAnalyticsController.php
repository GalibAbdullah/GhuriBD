<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\Payment;
use App\Models\ProviderVerification;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    private const TREND_MONTHS = 6;

    private const TOP_GUIDES_LIMIT = 5;

    public function index(): View
    {
        return view('admin.analytics.index', [
            'bookingStatusCounts' => $this->bookingStatusCounts(),
            'monthlyTrend' => $this->monthlyTrend(),
            'topGuides' => $this->topGuides(),
            'usersByRole' => $this->usersByRole(),
            'verificationCounts' => $this->verificationCounts(),
            'totalRevenue' => Payment::where('status', PaymentStatus::SUCCEEDED->value)->sum('amount'),
        ]);
    }

    private function bookingStatusCounts(): array
    {
        $counts = Booking::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(BookingStatus::cases())
            ->mapWithKeys(fn (BookingStatus $status) => [
                $status->value => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }

    /**
     * Booking volume and confirmed-payment revenue for each of the last
     * N months, computed in PHP rather than a driver-specific date-grouping
     * SQL function so it behaves the same on SQLite (tests) and MySQL (prod).
     */
    private function monthlyTrend(): array
    {
        $months = collect(range(self::TREND_MONTHS - 1, 0))
            ->map(fn (int $offset) => Carbon::now(GuideAvailability::timezone())->subMonths($offset)->startOfMonth());

        $bookingsByMonth = Booking::query()
            ->where('created_at', '>=', $months->first())
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->created_at->format('Y-m'));

        $revenueByMonth = Payment::query()
            ->where('status', PaymentStatus::SUCCEEDED->value)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $months->first())
            ->get()
            ->groupBy(fn (Payment $payment) => $payment->paid_at->format('Y-m'));

        return $months->map(function (Carbon $month) use ($bookingsByMonth, $revenueByMonth): array {
            $key = $month->format('Y-m');

            return [
                'label' => $month->format('M Y'),
                'bookings' => $bookingsByMonth->get($key, collect())->count(),
                'revenue' => (float) $revenueByMonth->get($key, collect())->sum('amount'),
            ];
        })->all();
    }

    /**
     * Top guides by confirmed-booking revenue. Booking is polymorphic, so this
     * walks bookable -> guide in PHP rather than a cross-table join that would
     * only work for one bookable type.
     */
    private function topGuides(): array
    {
        return Booking::query()
            ->where('status', BookingStatus::CONFIRMED->value)
            ->where('bookable_type', GuideAvailability::class)
            ->with('bookable.guide')
            ->get()
            ->filter(fn (Booking $booking) => $booking->bookable?->guide !== null)
            ->groupBy(fn (Booking $booking) => $booking->bookable->guide->id)
            ->map(function ($bookings) {
                $guide = $bookings->first()->bookable->guide;

                return [
                    'name' => $guide->name,
                    'bookings' => $bookings->count(),
                    'revenue' => (float) $bookings->sum('total_price'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(self::TOP_GUIDES_LIMIT)
            ->values()
            ->all();
    }

    private function usersByRole(): array
    {
        return collect(UserRole::cases())
            ->mapWithKeys(fn (UserRole $role) => [
                $role->value => User::role($role->value)->count(),
            ])
            ->all();
    }

    private function verificationCounts(): array
    {
        $counts = ProviderVerification::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(VerificationStatus::cases())
            ->mapWithKeys(fn (VerificationStatus $status) => [
                $status->value => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }
}
