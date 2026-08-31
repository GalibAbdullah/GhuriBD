@extends('layouts.app')

@section('title', 'Verification Details')
@section('page-title', 'Verification Details')

@section('sidebar')
    @if (auth()->user()->hasRole('Admin'))
        @include('partials.admin-sidebar')
    @else
        @include('partials.partner-sidebar')
    @endif
@endsection

@section('content')
    <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                @if (auth()->user()->hasRole('Admin'))
                    <li class="breadcrumb-item"><a href="{{ route('admin.verifications.index') }}" class="link-secondary link-underline-opacity-0">Verification queue</a></li>
                @else
                    <li class="breadcrumb-item"><a href="{{ route('partner.verifications.status') }}" class="link-secondary link-underline-opacity-0">Verification status</a></li>
                @endif
                <li class="breadcrumb-item active text-secondary" aria-current="page">Details</li>
            </ol>
        </nav>

        @if ($verification->isApproved())
            <span class="badge text-bg-success">Approved</span>
        @elseif ($verification->isRejected())
            <span class="badge text-bg-danger">Rejected</span>
        @else
            <span class="badge text-bg-warning">Pending</span>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4 h6 fw-semibold">{{ $verification->provider_name }}</h3>

                    <div style="max-width: 560px;">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-secondary">Provider type</span>
                                <span class="text-body fw-semibold">{{ $verification->provider_type }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-secondary">Business address</span>
                                <span class="text-end text-body">{{ $verification->business_address }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-secondary">Phone</span>
                                <span class="font-monospace fw-semibold">{{ $verification->phone }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-secondary">Submitted by</span>
                                <span class="text-body fw-semibold">{{ $verification->user->name }} ({{ $verification->user->email }})</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-secondary">Submitted at</span>
                                <span class="text-body">{{ $verification->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($verification->additional_information)
                        <div class="mt-3">
                            <div class="form-label mb-1">Additional information</div>
                            <div class="rounded-3 border bg-body-tertiary px-3 py-2 small text-body">
                                {{ $verification->additional_information }}
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ $verification->verification_document_url }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V9l-6-6z"/><path d="M14 3v6h6M9 13h6M9 17h6"/></svg>
                            View verification document
                        </a>
                    </div>

                    @if ($verification->isRejected() && $verification->rejection_reason)
                        <div class="mt-4 alert alert-danger mb-0">
                            <strong>Rejection reason:</strong> {{ $verification->rejection_reason }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if (auth()->user()->hasRole('Admin') && $verification->isPending())
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-4 h6 fw-semibold">Review decision</h3>

                        <form method="POST" action="{{ route('admin.verifications.review', $verification) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Decision</label>
                                <div class="form-check mb-2">
                                    <input type="radio" name="status" value="Approved" id="status_approved" class="form-check-input" required>
                                    <label for="status_approved" class="form-check-label small">Approve — mark partner as verified</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="status" value="Rejected" id="status_rejected" class="form-check-input" required>
                                    <label for="status_rejected" class="form-check-label small">Reject</label>
                                </div>
                                @error('status')<div class="text-danger small fw-medium mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="rejection_reason" class="form-label">Rejection reason <span class="text-body-tertiary fw-normal">(required when rejecting)</span></label>
                                <textarea id="rejection_reason" name="rejection_reason" rows="4" class="form-control @error('rejection_reason') is-invalid @enderror" placeholder="Explain why the request is being rejected">{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Submit decision</button>
                        </form>
                    </div>
                </div>
            @elseif ($verification->reviewed_at && $verification->reviewer)
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-4 h6 fw-semibold">Review info</h3>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-secondary">Reviewed by</span>
                                <span class="text-body fw-semibold">{{ $verification->reviewer->name }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between px-0 py-2">
                                <span class="text-secondary">Reviewed at</span>
                                <span class="text-body">{{ $verification->reviewed_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
