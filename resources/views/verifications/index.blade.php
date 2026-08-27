@extends('layouts.app')

@section('title', 'Verification Queue')
@section('page-title', 'Verification Queue')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('admin.verifications.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded active bg-primary-subtle text-primary-emphasis fw-semibold">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
        Verification
    </a>
@endsection

@section('content')
    <div class="mb-3 small text-secondary">
        Review and decide Travel Partner verification requests.
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Type</th>
                            <th>Partner</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($verifications as $verification)
                            <tr>
                                <td class="fw-semibold">{{ $verification->provider_name }}</td>
                                <td>{{ $verification->provider_type }}</td>
                                <td>
                                    {{ $verification->user->name }}
                                    <div class="small text-body-tertiary">{{ $verification->user->email }}</div>
                                </td>
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
                                    <a href="{{ route('admin.verifications.show', $verification) }}" class="small fw-semibold link-primary link-underline-opacity-0">
                                        {{ $verification->isPending() ? 'Review' : 'View' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
                                        <h3>No verification requests yet</h3>
                                        <p>When travel partners submit documents, they'll appear here for review.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($verifications->hasPages())
        <div class="mt-4">
            {{ $verifications->links() }}
        </div>
    @endif
@endsection
