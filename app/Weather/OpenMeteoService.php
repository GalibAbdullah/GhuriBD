<?php

namespace App\Weather;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Open-Meteo (open-meteo.com) — free, no API key required, no signup.
 */
class OpenMeteoService implements WeatherService
{
    private const BASE_URL = 'https://api.open-meteo.com/v1/forecast';

    private const CACHE_MINUTES = 30;

    /**
     * WMO weather interpretation codes, mapped to a human-readable
     * condition and a representative emoji.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const CONDITIONS = [
        0 => ['Clear sky', 'β˜€οΈ'],
        1 => ['Mainly clear', '🌀️'],
        2 => ['Partly cloudy', 'β›…'],
        3 => ['Overcast', 'β˜οΈ'],
        45 => ['Fog', '🌫️'],
        48 => ['Depositing rime fog', '🌫️'],
        51 => ['Light drizzle', '🌦️'],
        53 => ['Drizzle', '🌦️'],
        55 => ['Dense drizzle', '🌦️'],
        56 => ['Freezing drizzle', '🌦️'],
        57 => ['Dense freezing drizzle', '🌦️'],
        61 => ['Light rain', '🌧️'],
        63 => ['Rain', '🌧️'],
        65 => ['Heavy rain', '🌧️'],
        66 => ['Freezing rain', '🌧️'],
        67 => ['Heavy freezing rain', '🌧️'],
        71 => ['Light snow', '🌨️'],
        73 => ['Snow', '🌨️'],
        75 => ['Heavy snow', '🌨️'],
        77 => ['Snow grains', '🌨️'],
        80 => ['Light rain showers', '🌦️'],
        81 => ['Rain showers', '🌦️'],
        82 => ['Heavy rain showers', '🌧️'],
        85 => ['Snow showers', '🌨️'],
        86 => ['Heavy snow showers', '🌨️'],
        95 => ['Thunderstorm', 'β›ˆοΈ'],
        96 => ['Thunderstorm with hail', 'β›ˆοΈ'],
        99 => ['Severe thunderstorm with hail', 'β›ˆοΈ'],
    ];

    public function name(): string
    {
        return 'open-meteo';
    }

    public function forecast(float $latitude, float $longitude): ?WeatherForecast
    {
        $cacheKey = sprintf('weather.%.3f.%.3f', $latitude, $longitude);

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_MINUTES), function () use ($latitude, $longitude): ?WeatherForecast {
            return $this->fetch($latitude, $longitude);
        });
    }

    private function fetch(float $latitude, float $longitude): ?WeatherForecast
    {
        try {
            $response = Http::timeout(5)->get(self::BASE_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code,precipitation_probability',
                'daily' => 'weather_code,temperature_2m_max,precipitation_probability_max',
                'timezone' => 'auto',
                'forecast_days' => 4,
            ])->throw()->json();

            $current = $response['current'];
            $daily = $response['daily'];

            [$condition, $icon] = $this->describe((int) $current['weather_code']);

            return new WeatherForecast(
                date: now(),
                temperature: (float) $current['temperature_2m'],
                condition: $condition,
                icon: $icon,
                humidity: (int) $current['relative_humidity_2m'],
                windSpeed: (float) $current['wind_speed_10m'],
                rainProbability: (int) ($current['precipitation_probability'] ?? $daily['precipitation_probability_max'][0] ?? 0),
                days: $this->upcomingDays($daily),
            );
        } catch (\Throwable $e) {
            Log::warning('Weather forecast unavailable.', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * The 3 days after today from the daily block.
     *
     * @param  array<string, array<int, mixed>>  $daily
     * @return array<int, ForecastDay>
     */
    private function upcomingDays(array $daily): array
    {
        $days = [];

        foreach ($daily['time'] as $index => $date) {
            if ($index === 0) {
                continue; // Today — already represented by the "current" reading.
            }

            if (count($days) >= 3) {
                break;
            }

            [$condition, $icon] = $this->describe((int) $daily['weather_code'][$index]);

            $days[] = new ForecastDay(
                date: Carbon::parse($date),
                temperature: (float) $daily['temperature_2m_max'][$index],
                condition: $condition,
                icon: $icon,
            );
        }

        return $days;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function describe(int $code): array
    {
        return self::CONDITIONS[$code] ?? ['Unknown', '🌑️'];
    }
}
