<?php

namespace App\Http\Controllers;

use App\Enums\ProviderType;
use App\Enums\VerificationStatus;
use App\Http\Requests\ProviderVerificationRequest;
use App\Http\Requests\VerificationReviewRequest;
use App\Models\ProviderVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProviderVerificationController extends Controller
{
    /**
     * Display the authenticated Travel Partner's verification status.
     */
    public function status(): View
    {
        $user = request()->user();

        $verifications = $user->providerVerifications()
            ->latest()
            ->get();

        $latest = $verifications->first();

        return view('verifications.status', [
            'verifications' => $verifications,
            'latest' => $latest,
        ]);
    }

    /**
     * Show the form to create a new verification request.
     */
    public function create(): View
    {
        Gate::authorize('create', ProviderVerification::class);

        return view('verifications.create', [
            'providerTypes' => ProviderType::values(),
        ]);
    }

    /**
     * Store a new verification request for the authenticated Travel Partner.
     */
    public function store(ProviderVerificationRequest $request): RedirectResponse
    {
        Gate::authorize('create', ProviderVerification::class);

        $data = $request->safe()->except('verification_document');

        // Securely store the document on Laravel Storage (public disk).
        $data['verification_document'] = $request->file('verification_document')->store('verification-documents', 'public');

        $request->user()->providerVerifications()->create($data + [
            'status' => VerificationStatus::PENDING->value,
        ]);

        return redirect()
            ->route('partner.verifications.status')
            ->with('status', 'Verification request submitted successfully.');
    }

    /**
     * Show a single verification request. Accessible by the owning
     * Travel Partner and by Admins for review.
     */
    public function show(ProviderVerification $verification): View
    {
        Gate::authorize('view', $verification);

        return view('verifications.show', [
            'verification' => $verification->load(['user', 'reviewer']),
        ]);
    }

    /**
     * Display all verification requests for the Admin queue.
     */
    public function index(): View
    {
        $verifications = ProviderVerification::query()
            ->with(['user'])
            ->latest()
            ->paginate(15);

        return view('verifications.index', [
            'verifications' => $verifications,
        ]);
    }

    /**
     * Approve or reject a pending verification request.
     */
    public function review(VerificationReviewRequest $request, ProviderVerification $verification): RedirectResponse
    {
        Gate::authorize('review', $verification);

        $status = $request->validated('status');
        $isApproved = $status === VerificationStatus::APPROVED->value;

        $verification->update([
            'status' => $status,
            'rejection_reason' => $isApproved ? null : $request->validated('rejection_reason'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $message = $isApproved
            ? 'Verification approved. The Travel Partner is now verified.'
            : 'Verification rejected.';

        return redirect()
            ->route('admin.verifications.index')
            ->with('status', $message);
    }
}