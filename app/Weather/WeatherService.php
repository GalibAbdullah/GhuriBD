<?php

namespace App\Weather;

interface WeatherService
{
    /**
     * The provider's machine name — today it's Open-Meteo; a different
     * provider later is a new binding here, not a rewrite of every caller.
     */
    public function name(): string;

    /**
     * Current conditions plus a short outlook for the given coordinates.
     * Returns null if the forecast couldn't be retrieved (missing API key,
     * network failure, provider error) — callers must show a fallback.
     */
    public function forecast(float $latitude, float $longitude): ?WeatherForecast;
}
