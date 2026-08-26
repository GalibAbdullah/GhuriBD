@extends('layouts.app')

@section('title', 'Analytics')
@section('page-title', 'Analytics')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
    </a>
    <a href="{{ route('admin.verifications.index') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M12 3l8 3v6c0 4.5-3.4 7.9-8 9-4.6-1.1-8-4.5-8-9V6l8-3z"/></svg>
        Verification
    </a>
    <a href="{{ route('admin.analytics.index') }}" class="nav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M4 20V10M12 20V4M20 20v-7"/><path d="M2 20h20"/></svg>
        Analytics
    </a>
@endsection

@section('content')
    @php
        $maxRevenue = max(1, collect($monthlyTrend)->max('revenue'));
        $maxBookings = max(1, collect($monthlyTrend)->max('bookings'));
        $totalBookings = array_sum($bookingStatusCounts);
        $totalVerifications = array_sum($verificationCounts);
    @endphp

    <div class="mb-4 text-sm text-ink-muted">
        Platform-wide bookings, revenue, and verification trends.
    </div>

    <div class="mb-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card"><div class="stat-label">Lifetime revenue</div><div class="stat-value font-mono">৳{{ number_format($totalRevenue, 2) }}</div></div>
        @foreach ($usersByRole as $role => $count)
            <div class="stat-card"><div class="stat-label">{{ $role }}s</div><div class="stat-value">{{ $count }}</div></div>
        @endforeach
    </div>

    <div class="mb-6 grid gap-5 sm:grid-cols-2">
        <div class="card card-pad">
            <h3 class="mb-4 text-[16px] font-semibold">Revenue — last 6 months</h3>
            <div class="flex gap-3" style="height: 10rem">
                @foreach ($monthlyTrend as $month)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="flex w-full flex-1" style="align-items: flex-end">
                            <div class="w-full" style="height: {{ max(2, round($month['revenue'] / $maxRevenue * 100)) }}%; background-color: #0f6b5c; border-radius: 4px 4px 0 0" title="৳{{ number_format($month['revenue'], 2) }}"></div>
                        </div>
                        <div class="text-[11.5px] text-ink-faint">{{ $month['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card card-pad">
            <h3 class="mb-4 text-[16px] font-semibold">Bookings — last 6 months</h3>
            <div class="flex gap-3" style="height: 10rem">
                @foreach ($monthlyTrend as $month)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="flex w-full flex-1" style="align-items: flex-end">
                            <div class="w-full" style="height: {{ max(2, round($month['bookings'] / $maxBookings * 100)) }}%; background-color: #e4f1ee; border-radius: 4px 4px 0 0" title="{{ $month['bookings'] }} bookings"></div>
                        </div>
                        <div class="text-[11.5px] text-ink-faint">{{ $month['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mb-6 grid gap-5 sm:grid-cols-2">
        <div class="card card-pad">
            <h3 class="mb-4 text-[16px] font-semibold">Bookings by status</h3>
            @if ($totalBookings === 0)
                <p class="text-[12.5px] text-ink-faint">No bookings yet.</p>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($bookingStatusCounts as $status => $count)
                        <div>
                            <div class="mb-2 flex justify-between text-[12.5px]"><span>{{ $status }}</span><span class="font-mono">{{ $count }}</span></div>
                            <div class="w-full rounded-full bg-primary-tint" style="height: 0.5rem">
                                <div class="rounded-full" style="height: 0.5rem; width: {{ round($count / $totalBookings * 100) }}%; background-color: #0f6b5c"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card card-pad">
            <h3 class="mb-4 text-[16px] font-semibold">Provider verification funnel</h3>
            @if ($totalVerifications === 0)
                <p class="text-[12.5px] text-ink-faint">No verification requests yet.</p>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($verificationCounts as $status => $count)
                        <div>
                            <div class="mb-2 flex justify-between text-[12.5px]"><span>{{ $status }}</span><span class="font-mono">{{ $count }}</span></div>
                            <div class="w-full rounded-full bg-primary-tint" style="height: 0.5rem">
                                <div class="rounded-full" style="height: 0.5rem; width: {{ round($count / $totalVerifications * 100) }}%; background-color: #0f6b5c"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div>
        <h3 class="mb-4 text-[16px] font-semibold">Top guides by revenue</h3>
        <div class="card card-pad">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Guide</th>
                            <th>Confirmed bookings</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topGuides as $guide)
                            <tr>
                                <td class="font-semibold">{{ $guide['name'] }}</td>
                                <td>{{ $guide['bookings'] }}</td>
                                <td class="font-mono">৳{{ number_format($guide['revenue'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <h3>No confirmed bookings yet</h3>
                                        <p>Once travelers pay for bookings, top-earning guides will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
