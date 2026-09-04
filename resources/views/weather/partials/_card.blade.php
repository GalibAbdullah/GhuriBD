@php
    /** @var \App\Weather\WeatherForecast|null $forecast */
    $travelDate = $travelDate ?? null;
    $dayForecast = $forecast && $travelDate ? $forecast->forDate($travelDate) : null;
@endphp

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="h6 fw-semibold mb-0">Weather</h4>
            @if ($forecast)
                <span class="small text-secondary">{{ $forecast->date->format('M d, Y') }}</span>
            @endif
        </div>

        @if (! $forecast)
            <p class="mb-0 small text-secondary">Weather information is unavailable for this location right now.</p>
        @else
            @if ($forecast->hasSevereWeather())
                <div class="alert alert-warning py-2 px-3 small mb-3">
                    Heads up — {{ ucfirst($forecast->condition) }} expected. Plan accordingly.
                </div>
            @endif

            <div class="d-flex align-items-center gap-3 mb-3">
                <span style="font-size: 2.5rem; line-height: 1;" role="img" aria-label="{{ $forecast->condition }}">{{ $forecast->icon }}</span>
                <div>
                    <div class="fs-3 fw-bold">{{ round($forecast->temperature) }}&deg;C</div>
                    <div class="small text-secondary text-capitalize">{{ $forecast->condition }}</div>
                </div>
            </div>

            <div class="row g-2 text-center mb-3">
                <div class="col-4">
                    <div class="fw-semibold">{{ $forecast->humidity }}%</div>
                    <div class="small text-secondary">Humidity</div>
                </div>
                <div class="col-4">
                    <div class="fw-semibold">{{ $forecast->windSpeed }} m/s</div>
                    <div class="small text-secondary">Wind</div>
                </div>
                <div class="col-4">
                    <div class="fw-semibold">{{ $forecast->rainProbability }}%</div>
                    <div class="small text-secondary">Rain Chance</div>
                </div>
            </div>

            @if (! empty($forecast->days))
                <hr>
                <div class="small fw-semibold text-secondary mb-2">3-Day Forecast</div>
                <div class="row row-cols-3 g-2 text-center">
                    @foreach ($forecast->days as $day)
                        <div class="col">
                            <div class="small text-secondary">{{ $day->date->format('D') }}</div>
                            <div style="font-size: 1.5rem; line-height: 1;" role="img" aria-label="{{ $day->condition }}">{{ $day->icon }}</div>
                            <div class="small fw-semibold">{{ round($day->temperature) }}&deg;C</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($travelDate)
                <hr>
                @if ($dayForecast)
                    <div class="small text-secondary">
                        Forecast for your travel date ({{ $travelDate->format('M d') }}):
                        <strong class="text-body">{{ round($dayForecast->temperature) }}&deg;C, {{ $dayForecast->condition }}</strong>
                    </div>
                @else
                    <div class="small text-secondary">
                        Your travel date ({{ $travelDate->format('M d, Y') }}) is beyond our 3-day forecast window — check back closer to your trip.
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>
