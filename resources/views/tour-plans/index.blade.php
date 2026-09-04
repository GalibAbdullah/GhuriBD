@extends('layouts.app')

@section('title', 'AI Tour Planner')
@section('page-title', 'AI Tour Planner')

@section('sidebar')
    @include('traveler.partials.sidebar')
@endsection

@section('content')
    <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="small text-secondary">Itineraries generated for you, matched against real guide availability.</div>
        <a href="{{ route('tour-plans.create') }}" class="btn btn-primary btn-sm">New plan</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Destination</th>
                            <th>Days</th>
                            <th>Budget</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($plans as $plan)
                            <tr>
                                <td class="fw-semibold">{{ $plan->destination }}</td>
                                <td>{{ $plan->duration_days }}</td>
                                <td class="font-monospace">৳{{ number_format((float) $plan->budget, 2) }}</td>
                                <td>{{ $plan->created_at->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('tour-plans.show', $plan) }}" class="small fw-semibold link-primary link-underline-opacity-0">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 text-body-tertiary" width="40" height="40"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/></svg>
                                        <h3>No tour plans yet</h3>
                                        <p>Tell us your destination, budget, and interests to get a day-by-day plan.</p>
                                        <div class="mt-3">
                                            <a href="{{ route('tour-plans.create') }}" class="btn btn-primary btn-sm">Plan a trip</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($plans->hasPages())
        <div class="mt-4">
            {{ $plans->links() }}
        </div>
    @endif
@endsection
