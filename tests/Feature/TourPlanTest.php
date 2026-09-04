<?php

namespace Tests\Feature;

use App\Enums\Interest;
use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\TourPlan;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function traveler(): User
    {
        return $this->userWithRole(UserRole::TRAVELER->value);
    }

    private function verifiedGuide(): User
    {
        $guide = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->approved()->create([
            'user_id' => $guide->id,
            'provider_type' => ProviderType::TOUR_GUIDE->value,
        ]);

        return $guide;
    }

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'destination' => 'Sylhet',
            'days' => 3,
            'budget' => 10000,
            'interests' => [Interest::NATURE->value],
        ], $overrides);
    }

    // ---------------------------------------------------------------------
    // Access control
    // ---------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('tour-plans.index'))->assertRedirect('/login');
    }

    public function test_only_travelers_can_create_a_plan(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->post(route('tour-plans.store'), $this->basePayload())
            ->assertForbidden();
    }

    public function test_only_owner_can_view_a_plan(): void
    {
        $owner = $this->traveler();
        $stranger = $this->traveler();
        $plan = TourPlan::factory()->create(['traveler_id' => $owner->id]);

        $this->actingAs($stranger)->get(route('tour-plans.show', $plan))->assertForbidden();
        $this->actingAs($owner)->get(route('tour-plans.show', $plan))->assertOk();
    }

    public function test_only_owner_can_regenerate_or_delete_a_plan(): void
    {
        $owner = $this->traveler();
        $stranger = $this->traveler();
        $plan = TourPlan::factory()->create(['traveler_id' => $owner->id]);

        $this->actingAs($stranger)->post(route('tour-plans.regenerate', $plan))->assertForbidden();
        $this->actingAs($stranger)->delete(route('tour-plans.destroy', $plan))->assertForbidden();
    }

    // ---------------------------------------------------------------------
    // Validation
    // ---------------------------------------------------------------------

    public function test_generating_a_plan_requires_at_least_one_interest(): void
    {
        $this->actingAs($this->traveler())
            ->post(route('tour-plans.store'), $this->basePayload(['interests' => []]))
            ->assertSessionHasErrors('interests');
    }

    public function test_a_start_date_in_the_past_is_rejected(): void
    {
        $this->actingAs($this->traveler())
            ->post(route('tour-plans.store'), $this->basePayload(['start_date' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors('start_date');
    }

    // ---------------------------------------------------------------------
    // Generation
    // ---------------------------------------------------------------------

    public function test_generating_a_plan_creates_one_day_per_requested_day(): void
    {
        $response = $this->actingAs($this->traveler())
            ->post(route('tour-plans.store'), $this->basePayload(['days' => 4]));

        $plan = TourPlan::sole();
        $response->assertRedirect(route('tour-plans.show', $plan));
        $this->assertEquals(4, $plan->days()->count());
        $this->assertEquals([1, 2, 3, 4], $plan->days()->pluck('day_number')->all());
    }

    public function test_a_day_is_matched_to_an_affordable_bookable_guide_slot(): void
    {
        $guide = $this->verifiedGuide();

        $slot = GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'available_date' => now()->addDays(2)->toDateString(),
            'price' => 2000,
        ]);

        $this->actingAs($this->traveler())->post(route('tour-plans.store'), $this->basePayload([
            'days' => 3,
            'budget' => 30000, // daily budget after the 20% reserve is 8000 — well above the 2000 slot
            'start_date' => now()->addDay()->toDateString(),
        ]));

        $plan = TourPlan::sole();
        $matchedDay = $plan->days()->where('suggested_availability_id', $slot->id)->first();

        $this->assertNotNull($matchedDay, 'Expected one day to be matched to the affordable slot.');
    }

    public function test_a_slot_from_an_unverified_guide_is_never_suggested(): void
    {
        $unverifiedGuide = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        GuideAvailability::factory()->create([
            'user_id' => $unverifiedGuide->id,
            'available_date' => now()->addDays(2)->toDateString(),
            'price' => 500,
        ]);

        $this->actingAs($this->traveler())->post(route('tour-plans.store'), $this->basePayload(['budget' => 100000]));

        $plan = TourPlan::sole();
        $this->assertEquals(0, $plan->days()->whereNotNull('suggested_availability_id')->count());
    }

    public function test_an_unaffordable_slot_is_never_suggested(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'available_date' => now()->addDays(2)->toDateString(),
            'price' => 50000,
        ]);

        $this->actingAs($this->traveler())->post(route('tour-plans.store'), $this->basePayload(['budget' => 3000]));

        $plan = TourPlan::sole();
        $this->assertEquals(0, $plan->days()->whereNotNull('suggested_availability_id')->count());
    }

    public function test_regenerating_replaces_the_days_and_stamps_regenerated_at(): void
    {
        $traveler = $this->traveler();
        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 2]));
        $plan = TourPlan::sole();
        $originalDayIds = $plan->days()->pluck('id')->all();

        $this->actingAs($traveler)
            ->post(route('tour-plans.regenerate', $plan))
            ->assertRedirect(route('tour-plans.show', $plan));

        $plan->refresh();
        $this->assertNotNull($plan->regenerated_at);
        $this->assertEquals(2, $plan->days()->count());
        $this->assertEmpty(array_intersect($originalDayIds, $plan->days()->pluck('id')->all()));
    }

    public function test_owner_can_delete_a_plan(): void
    {
        $traveler = $this->traveler();
        $plan = TourPlan::factory()->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->delete(route('tour-plans.destroy', $plan))
            ->assertRedirect(route('tour-plans.index'));

        $this->assertModelMissing($plan);
    }
}
