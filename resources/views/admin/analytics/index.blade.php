@extends('layouts.app')

@section('title', 'Analytics')
@section('page-title', 'Analytics')

@section('sidebar')
    @include('partials.admin-sidebar')
@endsection

@php
    $maxRevenue = max(1, collect($monthlyTrend)->max('revenue'));
    $maxBookings = max(1, collect($monthlyTrend)->max('bookings'));
    $totalBookings = array_sum($bookingStatusCounts);
    $totalVerifications = array_sum($verificationCounts);
@endphp

@section('content')
    <div class="mb-4 small text-secondary">
        Platform-wide bookings, revenue, and verification trends.
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4 mb-4">
        <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">Lifetime revenue</div><div class="fs-3 fw-bold mt-2 font-monospace">৳{{ number_format($totalRevenue, 2) }}</div></div></div></div>
        @foreach ($usersByRole as $role => $count)
            <div class="col"><div class="card h-100"><div class="card-body"><div class="small text-secondary fw-semibold">{{ $role }}s</div><div class="fs-3 fw-bold mt-2">{{ $count }}</div></div></div></div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-4">Revenue — last 6 months</h3>
                    <div class="d-flex align-items-end gap-2" style="height: 10rem;">
                        @foreach ($monthlyTrend as $month)
                            <div class="d-flex flex-fill flex-column align-items-center justify-content-end gap-2 h-100">
                                <div class="w-100 bg-success rounded-top" style="height: {{ max(2, round($month['revenue'] / $maxRevenue * 100)) }}%;" title="৳{{ number_format($month['revenue'], 2) }}"></div>
                                <div class="small text-body-tertiary">{{ $month['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-4">Bookings — last 6 months</h3>
                    <div class="d-flex align-items-end gap-2" style="height: 10rem;">
                        @foreach ($monthlyTrend as $month)
                            <div class="d-flex flex-fill flex-column align-items-center justify-content-end gap-2 h-100">
                                <div class="w-100 bg-primary-subtle border border-primary rounded-top" style="height: {{ max(2, round($month['bookings'] / $maxBookings * 100)) }}%;" title="{{ $month['bookings'] }} bookings"></div>
                                <div class="small text-body-tertiary">{{ $month['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-4">Bookings by status</h3>
                    @if ($totalBookings === 0)
                        <p class="small text-body-tertiary mb-0">No bookings yet.</p>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach ($bookingStatusCounts as $status => $count)
                                <div>
                                    <div class="d-flex justify-content-between small mb-1"><span>{{ $status }}</span><span class="font-monospace">{{ $count }}</span></div>
                                    <div class="progress" style="height: 0.5rem;">
                                        <div class="progress-bar bg-success" style="width: {{ round($count / $totalBookings * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-4">Provider verification funnel</h3>
                    @if ($totalVerifications === 0)
                        <p class="small text-body-tertiary mb-0">No verification requests yet.</p>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach ($verificationCounts as $status => $count)
                                <div>
                                    <div class="d-flex justify-content-between small mb-1"><span>{{ $status }}</span><span class="font-monospace">{{ $count }}</span></div>
                                    <div class="progress" style="height: 0.5rem;">
                                        <div class="progress-bar bg-success" style="width: {{ round($count / $totalVerifications * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div>
        <h3 class="h6 fw-semibold mb-3">Top providers by confirmed-booking revenue</h3>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Confirmed bookings</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProviders as $provider)
                                <tr>
                                    <td class="fw-semibold">{{ $provider['name'] }}</td>
                                    <td>{{ $provider['bookings'] }}</td>
                                    <td class="font-monospace">৳{{ number_format($provider['revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <h3>No confirmed bookings yet</h3>
                                            <p>Once travelers book resorts and packages, top-earning providers will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
