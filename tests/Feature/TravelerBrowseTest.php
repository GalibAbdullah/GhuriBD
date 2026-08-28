<?php

namespace Tests\Feature;

use App\Enums\ResortStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\ProviderVerification;
use App\Models\Resort;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelerBrowseTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('traveler.resorts.index'))->assertRedirect('/login');
    }

    public function test_traveler_sees_only_active_resorts(): void
    {
        $partner = $this->verifiedPartner();
        Resort::factory()->create(['user_id' => $partner->id, 'name' => 'Active Resort', 'status' => ResortStatus::ACTIVE->value]);
        Resort::factory()->create(['user_id' => $partner->id, 'name' => 'Inactive Resort', 'status' => ResortStatus::INACTIVE->value]);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)
            ->get(route('traveler.resorts.index'))
            ->assertOk()
            ->assertSee('Active Resort')
            ->assertDontSee('Inactive Resort');
    }

    public function test_traveler_can_view_an_active_resort(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id, 'name' => 'Sea Pearl Resort', 'status' => ResortStatus::ACTIVE->value]);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)
            ->get(route('traveler.resorts.show', $resort))
            ->assertOk()
            ->assertSee('Sea Pearl Resort');
    }

    public function test_traveler_cannot_view_an_inactive_resort(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id, 'status' => ResortStatus::INACTIVE->value]);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)
            ->get(route('traveler.resorts.show', $resort))
            ->assertForbidden();
    }

    public function test_traveler_can_browse_rooms_of_an_active_resort(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id, 'status' => ResortStatus::ACTIVE->value]);
        Room::factory()->create(['resort_id' => $resort->id, 'room_name' => 'Ocean View Deluxe', 'status' => RoomStatus::AVAILABLE->value]);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)
            ->get(route('traveler.resorts.rooms.index', $resort))
            ->assertOk()
            ->assertSee('Ocean View Deluxe');
    }

    public function test_traveler_can_view_a_single_room(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id, 'status' => ResortStatus::ACTIVE->value]);
        $room = Room::factory()->create(['resort_id' => $resort->id, 'room_name' => 'Family Room Deluxe']);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)
            ->get(route('traveler.resorts.rooms.show', [$resort, $room]))
            ->assertOk()
            ->assertSee('Family Room Deluxe');
    }

    public function test_traveler_cannot_browse_rooms_of_an_inactive_resort(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id, 'status' => ResortStatus::INACTIVE->value]);
        $room = Room::factory()->create(['resort_id' => $resort->id]);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)->get(route('traveler.resorts.rooms.index', $resort))->assertForbidden();
        $this->actingAs($traveler)->get(route('traveler.resorts.rooms.show', [$resort, $room]))->assertForbidden();
    }

    public function test_traveler_cannot_manage_resorts_or_rooms(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id, 'status' => ResortStatus::ACTIVE->value]);
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)->get(route('partner.resorts.index'))->assertForbidden();
        $this->actingAs($traveler)->get(route('partner.resorts.rooms.index', $resort))->assertForbidden();
    }
}
