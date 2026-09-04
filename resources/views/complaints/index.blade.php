@extends('layouts.app')

@section('title', 'Complaints & Support')
@section('page-title', 'Complaints & Support')

@section('sidebar')
    @include('partials.role-sidebar')
@endsection

@php
    $statusColors = [
        'Open' => 'warning',
        'In Progress' => 'primary',
        'Resolved' => 'success',
        'Closed' => 'secondary',
    ];
@endphp

@section('content')
    <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="small text-secondary">
            {{ $isAdmin ? 'Every complaint filed on the platform.' : 'Complaints you\'ve filed and their status.' }}
        </div>

        @unless ($isAdmin)
            <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm">File a Complaint</a>
        @endunless
    </div>

    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('complaints.index') }}" class="btn btn-sm {{ $status ? 'btn-outline-secondary' : 'btn-secondary' }}">All</a>
        @foreach ($statuses as $option)
            <a href="{{ route('complaints.index', ['status' => $option]) }}" class="btn btn-sm {{ $status === $option ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $option }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body p-0">
            @forelse ($complaints as $complaint)
                <a href="{{ route('complaints.show', $complaint) }}" class="d-flex align-items-start justify-content-between gap-3 border-bottom px-3 py-3 text-decoration-none text-body {{ $loop->last ? 'border-bottom-0' : '' }}">
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">{{ $complaint->subject }}</span>
                            <span class="badge text-bg-light border">{{ $complaint->category }}</span>
                        </div>
                        <div class="mt-1 small text-secondary">
                            @if ($isAdmin)
                                {{ $complaint->user->name }} &middot;
                            @endif
                            Filed {{ $complaint->created_at->format('M d, Y') }}
                        </div>
                    </div>
                    <span class="badge text-bg-{{ $statusColors[$complaint->status] ?? 'secondary' }} flex-shrink-0">{{ $complaint->status }}</span>
                </a>
            @empty
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M5 21V4"/><path d="M5 4h13l-3 4 3 4H5"/></svg>
                    <h3>No complaints{{ $status ? " with status \"$status\"" : '' }}.</h3>
                    <p>{{ $isAdmin ? 'Nothing filed yet.' : "Run into a problem? File a complaint and we'll look into it." }}</p>
                </div>
            @endforelse
        </div>
    </div>

    @if ($complaints->hasPages())
        <div class="mt-4">
            {{ $complaints->links() }}
        </div>
    @endif
@endsection
