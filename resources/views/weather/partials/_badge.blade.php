@php
    $badgeForecast = $model->hasCoordinates()
        ? app(\App\Weather\WeatherService::class)->forecast((float) $model->latitude, (float) $model->longitude)
        : null;
@endphp

@if ($badgeForecast)
    <span class="badge text-bg-light border d-inline-flex align-items-center gap-1">
        <span role="img" aria-label="{{ $badgeForecast->condition }}">{{ $badgeForecast->icon }}</span>
        {{ round($badgeForecast->temperature) }}&deg;C
    </span>
@endif
