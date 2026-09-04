<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ProviderVerification;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    private const TREND_MONTHS = 6;

    private const TOP_PROVIDERS_LIMIT = 5;

    public function index(): View
    {
        return view('admin.analytics.index', [
            'bookingStatusCounts' => $this->bookingStatusCounts(),
            'monthlyTrend' => $this->monthlyTrend(),
            'topProviders' => $this->topProviders(),
            'usersByRole' => $this->usersByRole(),
            'verificationCounts' => $this->verificationCounts(),
            'totalRevenue' => (float) Payment::where('status', PaymentStatus::PAID->value)->sum('amount'),
        ]);
    }

    private function bookingStatusCounts(): array
    {
        $counts = Booking::query()
            ->selectRaw('booking_status, count(*) as total')
            ->groupBy('booking_status')
            ->pluck('total', 'booking_status');

        return collect(BookingStatus::cases())
            ->mapWithKeys(fn (BookingStatus $status) => [
                $status->value => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }

    /**
     * Booking volume and paid revenue for each of the last N months, computed
     * in PHP rather than a driver-specific date-grouping SQL function so it
     * behaves the same on SQLite (tests) and MySQL (prod).
     */
    private function monthlyTrend(): array
    {
        $months = collect(range(self::TREND_MONTHS - 1, 0))
            ->map(fn (int $offset) => Carbon::now(config('ghuribd.timezone'))->subMonths($offset)->startOfMonth());

        $bookingsByMonth = Booking::query()
            ->where('created_at', '>=', $months->first())
            ->get()
            ->groupBy(fn (Booking $booking) => $booking->created_at->format('Y-m'));

        $revenueByMonth = Payment::query()
            ->where('status', PaymentStatus::PAID->value)
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
     * Top Travel Partners by confirmed-booking revenue. A combined booking
     * touches both a resort and a tour package, so it credits both owners —
     * that traveler genuinely paid each of them for their part of the trip.
     */
    private function topProviders(): array
    {
        return Booking::query()
            ->where('booking_status', BookingStatus::CONFIRMED->value)
            ->with(['resort.user', 'tourPackage.user'])
            ->get()
            ->flatMap(function (Booking $booking): array {
                return collect([$booking->resort?->user, $booking->tourPackage?->user])
                    ->filter()
                    ->map(fn (User $owner) => ['owner' => $owner, 'amount' => (float) $booking->total_amount])
                    ->all();
            })
            ->groupBy(fn (array $entry) => $entry['owner']->id)
            ->map(function ($entries) {
                return [
                    'name' => $entries->first()['owner']->name,
                    'bookings' => $entries->count(),
                    'revenue' => collect($entries)->sum('amount'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(self::TOP_PROVIDERS_LIMIT)
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
