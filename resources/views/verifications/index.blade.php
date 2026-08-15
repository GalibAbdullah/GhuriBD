@extends('layouts.app')

@section('title', 'Verification Queue')
@section('page-title', 'Verification Queue')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('admin.verifications.index') }}" class="nav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
        Verification
    </a>
@endsection

@section('content')
    <div class="mb-4 text-sm text-ink-muted">
        Review and decide Travel Partner verification requests.
    </div>

    <div class="card card-pad">
        <div class="overflow-x-auto">
            <table class="table">
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
                            <td class="font-semibold">{{ $verification->provider_name }}</td>
                            <td>{{ $verification->provider_type }}</td>
                            <td>
                                {{ $verification->user->name }}
                                <div class="text-[11.5px] text-ink-faint">{{ $verification->user->email }}</div>
                            </td>
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
                                <a href="{{ route('admin.verifications.show', $verification) }}" class="text-[12.5px] font-semibold text-primary">
                                    {{ $verification->isPending() ? 'Review' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
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

    @if ($verifications->hasPages())
        <div class="mt-5">
            {{ $verifications->links() }}
        </div>
    @endif
@endsection