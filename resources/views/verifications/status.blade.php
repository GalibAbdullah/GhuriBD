@extends('layouts.app')

@section('title', 'Verification Status')
@section('page-title', 'Verification Status')

@section('sidebar')
    @include('partials.partner-sidebar')
@endsection

@section('content')
    <div class="mb-3 small text-secondary">
        Track your Travel Partner verification status.
    </div>

    @if ($latest)
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h3 class="h6 fw-semibold">Current verification: {{ $latest->provider_name }}</h3>

                    @if ($latest->isApproved())
                        <span class="badge text-bg-success">Approved</span>
                    @elseif ($latest->isRejected())
                        <span class="badge text-bg-danger">Rejected</span>
                    @else
                        <span class="badge text-bg-warning">Pending</span>
                    @endif
                </div>

                <div style="max-width: 480px;">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Provider type</span>
                            <span class="text-body fw-semibold">{{ $latest->provider_type }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Business address</span>
                            <span class="text-end text-body">{{ $latest->business_address }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Phone</span>
                            <span class="font-monospace fw-semibold">{{ $latest->phone }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-secondary">Submitted</span>
                            <span class="text-body">{{ $latest->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    @if ($latest->isRejected() && $latest->rejection_reason)
                        <div class="mt-3 alert alert-danger mb-0">
                            <strong>Rejection reason:</strong> {{ $latest->rejection_reason }}
                        </div>
                    @endif

                    @if ($latest->isApproved())
                        <div class="mt-3 alert alert-success mb-0">
                            <strong>Verified!</strong> Your Travel Partner account is verified.
                        </div>
                    @endif
                </div>

                <div class="mt-4 d-flex flex-wrap gap-3 align-items-center">
                    @if ($latest->isPending())
                        <span class="small text-secondary">Your request is awaiting admin review.</span>
                    @elseif ($latest->isApproved())
                        <span class="small text-secondary">You are verified. No further action is needed.</span>
                    @elseif ($latest->isRejected())
                        <a href="{{ route('partner.verifications.create') }}" class="btn btn-primary btn-sm">Submit a new request</a>
                    @endif

                    <a href="{{ route('partner.verifications.show', $latest) }}" class="btn btn-outline-secondary btn-sm">View details</a>
                </div>
            </div>
        </div>

        @if ($verifications->count() > 1)
            <div class="mb-3">
                <h3 class="h6">Previous submissions</h3>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
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
                                        <td class="fw-semibold">{{ $verification->provider_name }}</td>
                                        <td>{{ $verification->provider_type }}</td>
                                        <td>{{ $verification->created_at->format('M d, Y') }}</td>
                                        <td>
                                            @if ($verification->isApproved())
                                                <span class="badge text-bg-success">Approved</span>
                                            @elseif ($verification->isRejected())
                                                <span class="badge text-bg-danger">Rejected</span>
                                            @else
                                                <span class="badge text-bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('partner.verifications.show', $verification) }}" class="small fw-semibold link-primary link-underline-opacity-0">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
                    <h3>Not verified yet</h3>
                    <p>Submit your verification information to become a verified Travel Partner.</p>
                    <div class="mt-4">
                        <a href="{{ route('partner.verifications.create') }}" class="btn btn-primary">Submit verification</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
