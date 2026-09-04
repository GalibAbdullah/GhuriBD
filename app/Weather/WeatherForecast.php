<?php

namespace App\Weather;

use Illuminate\Support\Carbon;

/**
 * Current conditions for a location, plus the next few days' outlook.
 */
final readonly class WeatherForecast
{
    /**
     * @param  string  $icon  An emoji representing the condition (e.g. "β˜€οΈ"), not an image URL.
     * @param  array<int, ForecastDay>  $days  The upcoming days, not including today.
     */
    public function __construct(
        public Carbon $date,
        public float $temperature,
        public string $condition,
        public string $icon,
        public int $humidity,
        public float $windSpeed,
        public int $rainProbability,
        public array $days,
    ) {}

    /**
     * Heavy rain or a storm is brewing today, or the chance of rain is high
     * enough to warrant a heads-up before a trip.
     */
    public function hasSevereWeather(): bool
    {
        $severeTerms = ['thunderstorm', 'storm', 'heavy rain', 'extreme'];

        foreach ($severeTerms as $term) {
            if (str_contains(strtolower($this->condition), $term)) {
                return true;
            }
        }

        return $this->rainProbability >= 70;
    }

    /**
     * The forecast day matching the given date, if it falls within the
     * days we have data for.
     */
    public function forDate(Carbon $date): ?ForecastDay
    {
        if ($date->isSameDay($this->date)) {
            return new ForecastDay($this->date, $this->temperature, $this->condition, $this->icon);
        }

        foreach ($this->days as $day) {
            if ($day->date->isSameDay($date)) {
                return $day;
            }
        }

        return null;
    }
}
