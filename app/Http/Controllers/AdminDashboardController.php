<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\Payment;
use App\Models\ProviderVerification;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with live verification, booking, and
     * revenue data.
     */
    public function __invoke(): View
    {
        $pendingCount = ProviderVerification::query()
            ->where('status', VerificationStatus::PENDING->value)
            ->count();

        $approvedCount = ProviderVerification::query()
            ->where('status', VerificationStatus::APPROVED->value)
            ->count();

        $rejectedCount = ProviderVerification::query()
            ->where('status', VerificationStatus::REJECTED->value)
            ->count();

        $pendingVerifications = ProviderVerification::query()
            ->with(['user'])
            ->where('status', VerificationStatus::PENDING->value)
            ->latest()
            ->limit(10)
            ->get();

        $now = Carbon::now(GuideAvailability::timezone());

        $bookingsThisMonth = Booking::query()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();

        $revenueThisMonth = Payment::query()
            ->where('status', PaymentStatus::SUCCEEDED->value)
            ->whereNotNull('paid_at')
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->sum('amount');

        return view('dashboard.admin', [
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingVerifications' => $pendingVerifications,
            'bookingsThisMonth' => $bookingsThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'currentMonthLabel' => $now->format('M'),
        ]);
    }
}
