<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintStatus;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\ProviderVerification;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with live verification, booking, revenue,
     * and complaint data.
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

        $bookingsThisMonth = Booking::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $platformRevenue = (float) Payment::where('status', PaymentStatus::PAID->value)->sum('amount');

        $openComplaints = Complaint::query()
            ->whereIn('status', [ComplaintStatus::OPEN->value, ComplaintStatus::IN_PROGRESS->value])
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        $openComplaintsCount = Complaint::query()
            ->whereIn('status', [ComplaintStatus::OPEN->value, ComplaintStatus::IN_PROGRESS->value])
            ->count();

        return view('dashboard.admin', [
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingVerifications' => $pendingVerifications,
            'bookingsThisMonth' => $bookingsThisMonth,
            'platformRevenue' => $platformRevenue,
            'openComplaints' => $openComplaints,
            'openComplaintsCount' => $openComplaintsCount,
        ]);
    }
}
