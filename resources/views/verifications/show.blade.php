@extends('layouts.app')

@section('title', 'Verification Details')
@section('page-title', 'Verification Details')

@section('sidebar')
    @if (auth()->user()->hasRole('Admin'))
        <a href="{{ route('admin.dashboard') }}" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            Dashboard
        </a>
        <a href="{{ route('admin.verifications.index') }}" class="nav-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
            Verification
        </a>
    @else
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
    @endif
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="breadcrumb">
            @if (auth()->user()->hasRole('Admin'))
                <a href="{{ route('admin.verifications.index') }}">Verification queue</a>
                <span class="mx-1 text-ink-faint">/</span>
            @else
                <a href="{{ route('partner.verifications.status') }}">Verification status</a>
                <span class="mx-1 text-ink-faint">/</span>
            @endif
            <span>Details</span>
        </div>

        @if ($verification->isApproved())
            <span class="badge badge-success">Approved</span>
        @elseif ($verification->isRejected())
            <span class="badge badge-error">Rejected</span>
        @else
            <span class="badge badge-warning">Pending</span>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="card card-pad">
                <h3 class="mb-4 text-[16px] font-semibold">{{ $verification->provider_name }}</h3>

                <div class="max-w-[560px]">
                    <div class="kv-row">
                        <span class="kv-label">Provider type</span>
                        <span class="text-ink font-semibold">{{ $verification->provider_type }}</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Business address</span>
                        <span class="text-right text-ink">{{ $verification->business_address }}</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Phone</span>
                        <span class="kv-value">{{ $verification->phone }}</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Submitted by</span>
                        <span class="text-ink font-semibold">{{ $verification->user->name }} ({{ $verification->user->email }})</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Submitted at</span>
                        <span class="text-ink">{{ $verification->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>

                @if ($verification->additional_information)
                    <div class="mt-4">
                        <div class="input-label">Additional information</div>
                        <div class="rounded-lg border border-line-soft bg-bg px-4 py-3 text-[13px] text-ink">
                            {{ $verification->additional_information }}
                        </div>
                    </div>
                @endif

                <div class="mt-5">
                    <a href="{{ $verification->verification_document_url }}" target="_blank" class="btn btn-outline btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V9l-6-6z"/><path d="M14 3v6h6M9 13h6M9 17h6"/></svg>
                        View verification document
                    </a>
                </div>

                @if ($verification->isRejected() && $verification->rejection_reason)
                    <div class="mt-5 rounded-lg border border-error-tint bg-error-tint px-4 py-3 text-[13px] text-error">
                        <strong>Rejection reason:</strong> {{ $verification->rejection_reason }}
                    </div>
                @endif
            </div>
        </div>

        <div>
            @if (auth()->user()->hasRole('Admin') && $verification->isPending())
                <div class="card card-pad">
                    <h3 class="mb-4 text-[16px] font-semibold">Review decision</h3>

                    <form method="POST" action="{{ route('admin.verifications.review', $verification) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="input-label">Decision</label>
                            <label class="mb-2 flex cursor-pointer items-center gap-2.5 text-[13px] font-medium">
                                <input type="radio" name="status" value="Approved" class="accent-primary" required>
                                Approve — mark partner as verified
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 text-[13px] font-medium">
                                <input type="radio" name="status" value="Rejected" class="accent-error" required>
                                Reject
                            </label>
                            @error('status')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                        </div>

                        <div class="input-group">
                            <label for="rejection_reason" class="input-label">Rejection reason <span class="text-ink-faint font-normal">(required when rejecting)</span></label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="4" class="input @error('rejection_reason') !border-error @enderror" placeholder="Explain why the request is being rejected">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Submit decision</button>
                    </form>
                </div>
            @elseif ($verification->reviewed_at && $verification->reviewer)
                <div class="card card-pad">
                    <h3 class="mb-4 text-[16px] font-semibold">Review info</h3>
                    <div class="kv-row">
                        <span class="kv-label">Reviewed by</span>
                        <span class="text-ink font-semibold">{{ $verification->reviewer->name }}</span>
                    </div>
                    <div class="kv-row">
                        <span class="kv-label">Reviewed at</span>
                        <span class="text-ink">{{ $verification->reviewed_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection