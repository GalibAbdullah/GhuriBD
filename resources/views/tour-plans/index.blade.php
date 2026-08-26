@extends('layouts.app')

@section('title', 'AI Tour Planner')
@section('page-title', 'AI Tour Planner')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">Dashboard</a>
    <a href="{{ route('tour-plans.index') }}" class="nav-item active">AI Planner</a>
    <a href="{{ route('bookings.index') }}" class="nav-item">My Bookings</a>
@endsection

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div class="text-sm text-ink-muted">Itineraries generated for you, matched against real guide availability.</div>
        <a href="{{ route('tour-plans.create') }}" class="btn btn-primary btn-sm">New plan</a>
    </div>

    <div class="card card-pad">
        <div class="overflow-x-auto">
            <table class="table">
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
                            <td class="font-semibold">{{ $plan->destination }}</td>
                            <td>{{ $plan->duration_days }}</td>
                            <td class="font-mono">৳{{ number_format((float) $plan->budget, 2) }}</td>
                            <td>{{ $plan->created_at->format('M d, Y') }}</td>
                            <td class="text-right">
                                <a href="{{ route('tour-plans.show', $plan) }}" class="text-[12.5px] font-semibold text-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mx-auto mb-3 h-10 w-10 text-ink-faint"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/></svg>
                                    <h3>No tour plans yet</h3>
                                    <p>Tell us your destination, budget, and interests to get a day-by-day plan.</p>
                                    <div class="mt-4">
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

    @if ($plans->hasPages())
        <div class="mt-5">
            {{ $plans->links() }}
        </div>
    @endif
@endsection
