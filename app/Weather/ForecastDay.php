<?php

namespace App\Weather;

use Illuminate\Support\Carbon;

/**
 * One day of a multi-day forecast.
 */
final readonly class ForecastDay
{
    /**
     * @param  string  $icon  An emoji representing the condition (e.g. "β˜€οΈ"), not an image URL.
     */
    public function __construct(
        public Carbon $date,
        public float $temperature,
        public string $condition,
        public string $icon,
    ) {}
}
