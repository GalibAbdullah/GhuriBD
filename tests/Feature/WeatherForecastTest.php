<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Resort;
use App\Models\User;
use App\Weather\OpenMeteoService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherForecastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        Cache::flush();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['email' => fake()->unique()->safeEmail()]);
        $user->assignRole($role);

        return $user;
    }

    /**
     * Open-Meteo's response shape: a "current" block plus a 4-day "daily"
     * block (today + 3 upcoming days).
     *
     * @param  array<string, mixed>  $currentOverrides
     */
    private function fakeOpenMeteoResponse(array $currentOverrides = [], int $todaysWeatherCode = 0): array
    {
        return [
            'current' => array_merge([
                'temperature_2m' => 30.5,
                'relative_humidity_2m' => 65,
                'wind_speed_10m' => 3.5,
                'weather_code' => $todaysWeatherCode,
                'precipitation_probability' => 10,
            ], $currentOverrides),
            'daily' => [
                'time' => [
                    now()->toDateString(),
                    now()->addDay()->toDateString(),
                    now()->addDays(2)->toDateString(),
                    now()->addDays(3)->toDateString(),
                ],
                'weather_code' => [$todaysWeatherCode, 2, 2, 2],
                'temperature_2m_max' => [31.0, 29.0, 30.0, 31.5],
                'precipitation_probability_max' => [10, 10, 10, 10],
            ],
        ];
    }

    public function test_forecast_maps_current_conditions_correctly(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->fakeOpenMeteoResponse())]);

        $forecast = app(OpenMeteoService::class)->forecast(23.6850, 90.3563);

        $this->assertNotNull($forecast);
        $this->assertSame(30.5, $forecast->temperature);
        $this->assertSame('Clear sky', $forecast->condition);
        $this->assertSame(65, $forecast->humidity);
        $this->assertSame(3.5, $forecast->windSpeed);
        $this->assertSame(10, $forecast->rainProbability);
        $this->assertCount(3, $forecast->days);
    }

    public function test_forecast_is_cached_for_30_minutes(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->fakeOpenMeteoResponse())]);

        $service = app(OpenMeteoService::class);
        $service->forecast(23.6850, 90.3563);
        $service->forecast(23.6850, 90.3563);

        // Only the first call actually hit the API — the second was served from cache.
        Http::assertSentCount(1);
    }

    public function test_forecast_handles_api_failure_gracefully(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response(['reason' => 'bad request'], 400)]);

        $forecast = app(OpenMeteoService::class)->forecast(23.6850, 90.3563);

        $this->assertNull($forecast);
    }

    public function test_thunderstorm_is_flagged_as_severe_weather(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->fakeOpenMeteoResponse(todaysWeatherCode: 95))]);

        $forecast = app(OpenMeteoService::class)->forecast(23.6850, 90.3563);

        $this->assertSame('Thunderstorm', $forecast->condition);
        $this->assertTrue($forecast->hasSevereWeather());
    }

    public function test_high_rain_probability_is_flagged_as_severe_weather(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->fakeOpenMeteoResponse(['precipitation_probability' => 90]))]);

        $forecast = app(OpenMeteoService::class)->forecast(23.6850, 90.3563);

        $this->assertSame(90, $forecast->rainProbability);
        $this->assertTrue($forecast->hasSevereWeather());
    }

    public function test_clear_weather_is_not_flagged_as_severe(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->fakeOpenMeteoResponse())]);

        $forecast = app(OpenMeteoService::class)->forecast(23.6850, 90.3563);

        $this->assertFalse($forecast->hasSevereWeather());
    }

    public function test_resort_show_page_displays_weather_when_available(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response($this->fakeOpenMeteoResponse())]);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $resort = Resort::factory()->withCoordinates()->create();

        $response = $this->actingAs($traveler)->get(route('traveler.resorts.show', $resort));

        $response->assertOk();
        $response->assertSee('31', false);
        $response->assertSee('Clear sky');
        $response->assertSee('3-Day Forecast');
    }

    public function test_resort_show_page_shows_fallback_when_weather_unavailable(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response([], 500)]);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $resort = Resort::factory()->withCoordinates()->create();

        $response = $this->actingAs($traveler)->get(route('traveler.resorts.show', $resort));

        $response->assertOk();
        $response->assertSee('Weather information is unavailable for this location right now.');
    }

    public function test_resort_without_coordinates_shows_weather_fallback_without_calling_the_api(): void
    {
        Http::fake();

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $resort = Resort::factory()->create(['latitude' => null, 'longitude' => null]);

        $response = $this->actingAs($traveler)->get(route('traveler.resorts.show', $resort));

        $response->assertOk();
        $response->assertSee('Weather information is unavailable for this location right now.');
        Http::assertNothingSent();
    }
}
