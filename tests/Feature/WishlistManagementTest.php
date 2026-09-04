<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Resort;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\Wishlist;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role, ?string $email = null): User
    {
        $user = User::factory()->create([
            'email' => $email ?? fake()->unique()->safeEmail(),
        ]);

        $user->assignRole($role);

        return $user;
    }

    // --- Access control -----------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('traveler.wishlist.index'))->assertRedirect('/login');
    }

    public function test_travel_partner_cannot_use_wishlist(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $resort = Resort::factory()->create();

        $this->actingAs($partner)->get(route('traveler.wishlist.index'))->assertForbidden();
        $this->actingAs($partner)->post(route('wishlist.resorts.toggle', $resort))->assertForbidden();
    }

    // --- Behavior -------------------------------------------------------

    public function test_traveler_can_add_and_remove_a_resort_from_wishlist(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $resort = Resort::factory()->create();

        $this->actingAs($traveler)
            ->post(route('wishlist.resorts.toggle', $resort))
            ->assertRedirect();

        $this->assertDatabaseHas('wishlists', ['user_id' => $traveler->id, 'resort_id' => $resort->id]);

        $this->actingAs($traveler)
            ->post(route('wishlist.resorts.toggle', $resort))
            ->assertRedirect();

        $this->assertDatabaseMissing('wishlists', ['user_id' => $traveler->id, 'resort_id' => $resort->id]);
    }

    public function test_traveler_can_add_and_remove_a_tour_package_from_wishlist(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $package = TourPackage::factory()->create();

        $this->actingAs($traveler)->post(route('wishlist.packages.toggle', $package));

        $this->assertDatabaseHas('wishlists', ['user_id' => $traveler->id, 'tour_package_id' => $package->id]);

        $this->actingAs($traveler)->post(route('wishlist.packages.toggle', $package));

        $this->assertDatabaseMissing('wishlists', ['user_id' => $traveler->id, 'tour_package_id' => $package->id]);
    }

    public function test_duplicate_wishlist_entry_is_prevented(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $resort = Resort::factory()->create();

        $this->actingAs($traveler)->post(route('wishlist.resorts.toggle', $resort));
        $this->actingAs($traveler)->post(route('wishlist.resorts.toggle', $resort));
        $this->actingAs($traveler)->post(route('wishlist.resorts.toggle', $resort));

        // Odd number of toggles (3) — the item ends up saved, exactly once.
        $this->assertSame(1, Wishlist::where('user_id', $traveler->id)->where('resort_id', $resort->id)->count());
    }

    public function test_wishlist_shows_only_the_owners_items(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $otherTraveler = $this->userWithRole(UserRole::TRAVELER->value);

        $ownResort = Resort::factory()->create();
        $otherResort = Resort::factory()->create();

        Wishlist::factory()->create(['user_id' => $traveler->id, 'resort_id' => $ownResort->id, 'tour_package_id' => null]);
        Wishlist::factory()->create(['user_id' => $otherTraveler->id, 'resort_id' => $otherResort->id, 'tour_package_id' => null]);

        $response = $this->actingAs($traveler)->get(route('traveler.wishlist.index'));

        $response->assertOk();
        $response->assertSee($ownResort->name);
        $response->assertDontSee($otherResort->name);
    }

    public function test_wishlist_can_be_filtered_by_type(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $resort = Resort::factory()->create();
        $package = TourPackage::factory()->create();

        Wishlist::factory()->create(['user_id' => $traveler->id, 'resort_id' => $resort->id, 'tour_package_id' => null]);
        Wishlist::factory()->create(['user_id' => $traveler->id, 'resort_id' => null, 'tour_package_id' => $package->id]);

        $response = $this->actingAs($traveler)->get(route('traveler.wishlist.index', ['type' => 'resorts']));
        $response->assertSee($resort->name);
        $response->assertDontSee($package->title);

        $response = $this->actingAs($traveler)->get(route('traveler.wishlist.index', ['type' => 'packages']));
        $response->assertSee($package->title);
        $response->assertDontSee($resort->name);
    }
}
