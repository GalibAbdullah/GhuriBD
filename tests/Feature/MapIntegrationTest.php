<?php

namespace Tests\Feature;

use App\Enums\ResortAmenity;
use App\Enums\UserRole;
use App\Models\ProviderVerification;
use App\Models\Resort;
use App\Models\TourPackage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MapIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['email' => fake()->unique()->safeEmail()]);
        $user->assignRole($role);

        return $user;
    }

    private function verifiedPartner(): User
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->approved()->create(['user_id' => $partner->id]);

        return $partner;
    }

    public function test_travel_partner_can_save_coordinates_when_creating_a_resort(): void
    {
        $partner = $this->verifiedPartner();

        $response = $this->actingAs($partner)->post(route('partner.resorts.store'), [
            'name' => 'Sea Pearl Resort',
            'description' => 'A beachfront resort with stunning views.',
            'division' => 'Chattogram',
            'district' => "Cox's Bazar",
            'address' => "Marine Drive Road, Cox's Bazar",
            'latitude' => 21.4272,
            'longitude' => 92.0058,
            'contact_phone' => '01700000000',
            'price_range' => '৳3000 - ৳8000',
            'amenities' => [ResortAmenity::WIFI->value],
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect();

        $resort = Resort::firstOrFail();
        $this->assertEquals(21.4272, (float) $resort->latitude);
        $this->assertEquals(92.0058, (float) $resort->longitude);
        $this->assertTrue($resort->hasCoordinates());
    }

    public function test_resort_coordinates_are_optional(): void
    {
        $partner = $this->verifiedPartner();

        $response = $this->actingAs($partner)->post(route('partner.resorts.store'), [
            'name' => 'Sea Pearl Resort',
            'description' => 'A beachfront resort with stunning views.',
            'division' => 'Chattogram',
            'district' => "Cox's Bazar",
            'address' => "Marine Drive Road, Cox's Bazar",
            'contact_phone' => '01700000000',
            'price_range' => '৳3000 - ৳8000',
            'amenities' => [ResortAmenity::WIFI->value],
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect();

        $resort = Resort::firstOrFail();
        $this->assertFalse($resort->hasCoordinates());
        $this->assertNull($resort->googleMapsUrl());
    }

    public function test_invalid_coordinates_are_rejected(): void
    {
        $partner = $this->verifiedPartner();

        $response = $this->actingAs($partner)->post(route('partner.resorts.store'), [
            'name' => 'Sea Pearl Resort',
            'description' => 'A beachfront resort with stunning views.',
            'division' => 'Chattogram',
            'district' => "Cox's Bazar",
            'address' => "Marine Drive Road, Cox's Bazar",
            'latitude' => 200,
            'longitude' => 92.0058,
            'contact_phone' => '01700000000',
            'price_range' => '৳3000 - ৳8000',
            'amenities' => [ResortAmenity::WIFI->value],
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertSessionHasErrors('latitude');
    }

    public function test_resort_map_loads_correctly_on_traveler_show_page(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $resort = Resort::factory()->withCoordinates()->create();

        $response = $this->actingAs($traveler)->get(route('traveler.resorts.show', $resort));

        $response->assertOk();
        $response->assertSee('Open in Google Maps');
        $response->assertSee((string) $resort->latitude, false);
    }

    public function test_resort_show_page_handles_missing_coordinates_gracefully(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $resort = Resort::factory()->create(['latitude' => null, 'longitude' => null]);

        $response = $this->actingAs($traveler)->get(route('traveler.resorts.show', $resort));

        $response->assertOk();
        $response->assertSee('Location not pinned on the map yet.');
    }

    public function test_travel_partner_can_save_coordinates_when_creating_a_tour_package(): void
    {
        $partner = $this->verifiedPartner();

        $response = $this->actingAs($partner)->post(route('partner.packages.store'), [
            'title' => 'Sundarbans Wildlife Safari',
            'destination' => 'Sundarbans',
            'division' => 'Khulna',
            'district' => 'Khulna',
            'description' => 'A wildlife safari through the mangroves.',
            'duration_days' => 2,
            'duration_nights' => 1,
            'price' => 5000,
            'max_travelers' => 10,
            'meeting_point' => 'Khulna Bus Terminal',
            'start_location' => 'Khulna',
            'latitude' => 22.4979,
            'longitude' => 89.5403,
            'itinerary' => 'Day 1: Arrival. Day 2: Safari.',
            'status' => 'Active',
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect();

        $package = TourPackage::firstOrFail();
        $this->assertEquals(22.4979, (float) $package->latitude);
        $this->assertEquals(89.5403, (float) $package->longitude);
    }

    public function test_tour_package_map_loads_correctly_on_traveler_show_page(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $package = TourPackage::factory()->withCoordinates()->create();

        $response = $this->actingAs($traveler)->get(route('traveler.packages.show', $package));

        $response->assertOk();
        $response->assertSee('Open in Google Maps');
    }

    public function test_search_card_shows_view_on_map_button_only_when_coordinates_exist(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        Resort::factory()->withCoordinates()->create(['name' => 'Pinned Resort']);
        Resort::factory()->create(['name' => 'Unpinned Resort', 'latitude' => null, 'longitude' => null]);

        $response = $this->actingAs($traveler)->get(route('search.results', ['tab' => 'resorts']));

        $response->assertOk();
        $response->assertSee('Pinned Resort');
        $response->assertSee('Unpinned Resort');
        $response->assertSee('View on Map');
    }
}
