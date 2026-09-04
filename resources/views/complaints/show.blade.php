@extends('layouts.app')

@section('title', $complaint->subject)
@section('page-title', 'Complaint Details')

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
    $isAdmin = auth()->user()->hasRole('Admin');
@endphp

@section('content')
    <div class="mb-3">
        <a href="{{ route('complaints.index') }}" class="small fw-semibold link-secondary link-underline-opacity-0">&larr; Back to Complaints</a>
    </div>

    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h3 class="h5 mb-1">{{ $complaint->subject }}</h3>
            <div class="small text-secondary">Filed {{ $complaint->created_at->format('M d, Y') }} by {{ $complaint->user->name }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-light border">{{ $complaint->category }}</span>
            <span class="badge text-bg-{{ $statusColors[$complaint->status] ?? 'secondary' }}">{{ $complaint->status }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="h6 fw-semibold mb-2">Description</h4>
                    <p class="mb-0 text-body small" style="white-space: pre-wrap;">{{ $complaint->description }}</p>
                </div>
            </div>

            @if ($complaint->booking)
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-2">Related Booking</h4>
                        <div class="small text-secondary font-monospace">{{ $complaint->booking->booking_reference }}</div>
                    </div>
                </div>
            @endif

            @if ($complaint->admin_response)
                <div class="card border-success mb-4">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-2 text-success">Support Response</h4>
                        <p class="mb-1 text-body small" style="white-space: pre-wrap;">{{ $complaint->admin_response }}</p>
                        @if ($complaint->resolver)
                            <div class="small text-secondary">— {{ $complaint->resolver->name }}, {{ $complaint->resolved_at?->format('M d, Y') }}</div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($isAdmin)
                <div class="card">
                    <div class="card-body">
                        <h4 class="h6 fw-semibold mb-3">Respond</h4>
                        <form method="POST" action="{{ route('admin.complaints.respond', $complaint) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="status" class="form-label small fw-semibold">Status</label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach ($statuses as $option)
                                        <option value="{{ $option }}" @selected(old('status', $complaint->status) === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="admin_response" class="form-label small fw-semibold">Response</label>
                                <textarea name="admin_response" id="admin_response" rows="4" maxlength="5000" class="form-control @error('admin_response') is-invalid @enderror" placeholder="Write a response to the complainant">{{ old('admin_response', $complaint->admin_response) }}</textarea>
                                @error('admin_response')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">Send Response</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="small text-secondary fw-semibold">Filed by</div>
                    <div class="mt-1 fw-semibold">{{ $complaint->user->name }}</div>
                    <div class="small text-secondary">{{ $complaint->user->email }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
