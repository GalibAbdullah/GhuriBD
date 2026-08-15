<?php

namespace App\Http\Controllers;

use App\Enums\VerificationStatus;
use App\Models\ProviderVerification;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with live verification data.
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

        return view('dashboard.admin', [
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingVerifications' => $pendingVerifications,
        ]);
    }
}