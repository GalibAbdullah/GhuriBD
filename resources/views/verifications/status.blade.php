@extends('layouts.app')

@section('title', 'Verification Status')
@section('page-title', 'Verification Status')

@section('sidebar')
    <a href="{{ route('partner.dashboard') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('profile.show') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        My Profile
    </a>
    <a href="{{ route('partner.verifications.status') }}" class="nav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
        Verification
    </a>
@endsection

@section('content')
    <div class="mb-4 text-sm text-ink-muted">
        Track your Travel Partner verification status.
    </div>

    @if ($latest)
        <div class="card card-pad mb-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-[16px] font-semibold">Current verification: {{ $latest->provider_name }}</h3>

                @if ($latest->isApproved())
                    <span class="badge badge-success">Approved</span>
                @elseif ($latest->isRejected())
                    <span class="badge badge-error">Rejected</span>
                @else
                    <span class="badge badge-warning">Pending</span>
                @endif
            </div>

            <div class="max-w-[480px]">
                <div class="kv-row">
                    <span class="kv-label">Provider type</span>
                    <span class="text-ink font-semibold">{{ $latest->provider_type }}</span>
                </div>
                <div class="kv-row">
                    <span class="kv-label">Business address</span>
                    <span class="text-right text-ink">{{ $latest->business_address }}</span>
                </div>
                <div class="kv-row">
                    <span class="kv-label">Phone</span>
                    <span class="kv-value">{{ $latest->phone }}</span>
                </div>
                <div class="kv-row">
                    <span class="kv-label">Submitted</span>
                    <span class="text-ink">{{ $latest->created_at->format('M d, Y') }}</span>
                </div>

                @if ($latest->isRejected() && $latest->rejection_reason)
                    <div class="mt-4 rounded-lg border border-error-tint bg-error-tint px-4 py-3 text-[13px] text-error">
                        <strong>Rejection reason:</strong> {{ $latest->rejection_reason }}
                    </div>
                @endif

                @if ($latest->isApproved())
                    <div class="mt-4 rounded-lg border border-success-tint bg-success-tint px-4 py-3 text-[13px] text-success">
                        <strong>Verified!</strong> Your Travel Partner account is verified.
                    </div>
                @endif
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                @if ($latest->isPending())
                    <span class="text-[12.5px] text-ink-muted">Your request is awaiting admin review.</span>
                @elseif ($latest->isApproved())
                    <span class="text-[12.5px] text-ink-muted">You are verified. No further action is needed.</span>
                @elseif ($latest->isRejected())
                    <a href="{{ route('partner.verifications.create') }}" class="btn btn-primary btn-sm">Submit a new request</a>
                @endif

                <a href="{{ route('partner.verifications.show', $latest) }}" class="btn btn-outline btn-sm">View details</a>
            </div>
        </div>

        @if ($verifications->count() > 1)
            <div class="mb-4">
                <h3 class="text-[16px]">Previous submissions</h3>
            </div>
            <div class="card card-pad">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Type</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($verifications->skip(1) as $verification)
                                <tr>
                                    <td class="font-semibold">{{ $verification->provider_name }}</td>
                                    <td>{{ $verification->provider_type }}</td>
                                    <td>{{ $verification->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if ($verification->isApproved())
                                            <span class="badge badge-success">Approved</span>
                                        @elseif ($verification->isRejected())
                                            <span class="badge badge-error">Rejected</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('partner.verifications.show', $verification) }}" class="text-[12.5px] font-semibold text-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @else
        <div class="card card-pad">
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
                <h3>Not verified yet</h3>
                <p>Submit your verification information to become a verified Travel Partner.</p>
                <div class="mt-5">
                    <a href="{{ route('partner.verifications.create') }}" class="btn btn-primary">Submit verification</a>
                </div>
            </div>
        </div>
    @endif
@endsection