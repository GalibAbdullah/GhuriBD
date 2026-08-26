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

    public function test_only_owner_can_regenerate_a_plan(): void
    {
        $owner = $this->traveler();
        $stranger = $this->traveler();
        $plan = TourPlan::factory()->create(['traveler_id' => $owner->id]);

        $this->actingAs($stranger)->post(route('tour-plans.regenerate', $plan))->assertForbidden();
    }

    public function test_only_owner_can_delete_a_plan(): void
    {
        $owner = $this->traveler();
        $stranger = $this->traveler();
        $plan = TourPlan::factory()->create(['traveler_id' => $owner->id]);

        $this->actingAs($stranger)->delete(route('tour-plans.destroy', $plan))->assertForbidden();
        $this->assertDatabaseHas('tour_plans', ['id' => $plan->id]);
    }

    public function test_index_only_lists_the_current_travelers_plans(): void
    {
        $traveler = $this->traveler();
        $other = $this->traveler();
        $mine = TourPlan::factory()->create(['traveler_id' => $traveler->id, 'destination' => 'Mine Destination']);
        TourPlan::factory()->create(['traveler_id' => $other->id, 'destination' => 'Other Destination']);

        $this->actingAs($traveler)
            ->get(route('tour-plans.index'))
            ->assertOk()
            ->assertSee('Mine Destination')
            ->assertDontSee('Other Destination');
    }

    // ---------------------------------------------------------------------
    // Validation
    // ---------------------------------------------------------------------

    public function test_destination_is_required(): void
    {
        $this->actingAs($this->traveler())
            ->post(route('tour-plans.store'), $this->basePayload(['destination' => '']))
            ->assertSessionHasErrors('destination');
    }

    public function test_days_must_be_within_configured_bounds(): void
    {
        $rules = config('ghuribd.tour_planner');
        $traveler = $this->traveler();

        $this->actingAs($traveler)
            ->post(route('tour-plans.store'), $this->basePayload(['days' => $rules['min_days'] - 1]))
            ->assertSessionHasErrors('days');

        $this->actingAs($traveler)
            ->post(route('tour-plans.store'), $this->basePayload(['days' => $rules['max_days'] + 1]))
            ->assertSessionHasErrors('days');
    }

    public function test_budget_must_be_within_configured_bounds(): void
    {
        $rules = config('ghuribd.tour_planner');
        $traveler = $this->traveler();

        $this->actingAs($traveler)
            ->post(route('tour-plans.store'), $this->basePayload(['budget' => $rules['min_budget'] - 1]))
            ->assertSessionHasErrors('budget');

        $this->actingAs($traveler)
            ->post(route('tour-plans.store'), $this->basePayload(['budget' => $rules['max_budget'] + 1]))
            ->assertSessionHasErrors('budget');
    }

    public function test_at_least_one_interest_is_required(): void
    {
        $this->actingAs($this->traveler())
            ->post(route('tour-plans.store'), $this->basePayload(['interests' => []]))
            ->assertSessionHasErrors('interests');
    }

    public function test_interests_must_be_known_values(): void
    {
        $this->actingAs($this->traveler())
            ->post(route('tour-plans.store'), $this->basePayload(['interests' => ['Not A Real Interest']]))
            ->assertSessionHasErrors('interests.0');
    }

    public function test_start_date_cannot_be_in_the_past(): void
    {
        $this->actingAs($this->traveler())
            ->post(route('tour-plans.store'), $this->basePayload([
                'start_date' => GuideAvailability::today()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('start_date');
    }

    // ---------------------------------------------------------------------
    // Generation — structure and budget
    // ---------------------------------------------------------------------

    public function test_generates_exactly_one_day_row_per_requested_day(): void
    {
        $traveler = $this->traveler();

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 5]));

        $plan = TourPlan::firstOrFail();
        $this->assertSame(5, $plan->days()->count());
        $this->assertSame([1, 2, 3, 4, 5], $plan->days()->pluck('day_number')->all());
    }

    public function test_daily_budget_reserves_logistics_percentage_off_the_top(): void
    {
        $traveler = $this->traveler();
        $reservePercent = config('ghuribd.tour_planner.logistics_reserve_percent');

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload([
            'budget' => 10000, 'days' => 5,
        ]));

        $plan = TourPlan::firstOrFail();
        $expectedDaily = bcdiv(bcmul('10000', (string) (1 - $reservePercent / 100), 2), '5', 2);

        $plan->days->each(function ($day) use ($expectedDaily) {
            $this->assertSame($expectedDaily, $day->budget_allocated);
        });
    }

    public function test_themes_cycle_through_selected_interests(): void
    {
        $traveler = $this->traveler();

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload([
            'days' => 4,
            'interests' => [Interest::NATURE->value, Interest::FOOD->value],
        ]));

        $themes = TourPlan::firstOrFail()->days()->pluck('theme')->all();

        $this->assertSame([
            Interest::NATURE->value,
            Interest::FOOD->value,
            Interest::NATURE->value,
            Interest::FOOD->value,
        ], $themes);
    }

    // ---------------------------------------------------------------------
    // Guide matching
    // ---------------------------------------------------------------------

    public function test_matches_a_real_affordable_guide_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        // Daily budget for budget=10000/days=1 at 20% reserve = 8000.00.
        $slot = GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'price' => 5000,
        ]);

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 1, 'budget' => 10000]));

        $day = TourPlan::firstOrFail()->days()->firstOrFail();
        $this->assertSame($slot->id, $day->suggested_availability_id);
    }

    public function test_does_not_suggest_a_slot_priced_above_the_daily_budget(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        // Daily budget = 800.00; slot costs far more.
        GuideAvailability::factory()->create(['user_id' => $guide->id, 'price' => 50000]);

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 1, 'budget' => 1000]));

        $day = TourPlan::firstOrFail()->days()->firstOrFail();
        $this->assertNull($day->suggested_availability_id);
        $this->assertStringContainsString('No guides are currently listed', $day->description);
    }

    public function test_does_not_suggest_a_slot_from_an_unapproved_provider(): void
    {
        $notYetVerified = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        ProviderVerification::factory()->pending()->create([
            'user_id' => $notYetVerified->id,
            'provider_type' => ProviderType::TOUR_GUIDE->value,
        ]);
        GuideAvailability::factory()->create(['user_id' => $notYetVerified->id, 'price' => 1000]);

        $traveler = $this->traveler();
        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 1, 'budget' => 10000]));

        $this->assertNull(TourPlan::firstOrFail()->days()->firstOrFail()->suggested_availability_id);
    }

    public function test_does_not_suggest_a_slot_from_a_non_guide_provider_type(): void
    {
        $resortOwner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        ProviderVerification::factory()->approved()->create([
            'user_id' => $resortOwner->id,
            'provider_type' => ProviderType::RESORT_OWNER->value,
        ]);
        GuideAvailability::factory()->create(['user_id' => $resortOwner->id, 'price' => 1000]);

        $traveler = $this->traveler();
        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 1, 'budget' => 10000]));

        $this->assertNull(TourPlan::firstOrFail()->days()->firstOrFail()->suggested_availability_id);
    }

    public function test_does_not_reuse_the_same_slot_across_two_days(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        $only = GuideAvailability::factory()->create(['user_id' => $guide->id, 'price' => 1000]);

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 2, 'budget' => 10000]));

        $days = TourPlan::firstOrFail()->days;
        $assigned = $days->pluck('suggested_availability_id')->filter();

        $this->assertCount(1, $assigned);
        $this->assertTrue($assigned->contains($only->id));
    }

    public function test_matches_the_exact_calendar_date_when_a_start_date_is_given(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $startDate = GuideAvailability::today()->addDays(5);

        $dayTwoSlot = GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'price' => 1000,
            'available_date' => $startDate->copy()->addDay()->toDateString(),
        ]);
        // A cheaper, earlier slot that does NOT match any day's exact date —
        // must never be substituted in, or the itinerary's dates would lie.
        GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'price' => 500,
            'available_date' => $startDate->copy()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload([
            'days' => 2, 'budget' => 10000, 'start_date' => $startDate->toDateString(),
        ]));

        $days = TourPlan::firstOrFail()->days()->orderBy('day_number')->get();
        $this->assertNull($days[0]->suggested_availability_id);
        $this->assertSame($dayTwoSlot->id, $days[1]->suggested_availability_id);
    }

    public function test_without_a_start_date_slots_are_assigned_in_chronological_order(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        $later = GuideAvailability::factory()->create([
            'user_id' => $guide->id, 'price' => 1000,
            'available_date' => GuideAvailability::today()->addDays(20)->toDateString(),
        ]);
        $sooner = GuideAvailability::factory()->create([
            'user_id' => $guide->id, 'price' => 1000,
            'available_date' => GuideAvailability::today()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 2, 'budget' => 10000]));

        $days = TourPlan::firstOrFail()->days()->orderBy('day_number')->get();
        $this->assertSame($sooner->id, $days[0]->suggested_availability_id);
        $this->assertSame($later->id, $days[1]->suggested_availability_id);
    }

    // ---------------------------------------------------------------------
    // Regenerate & delete
    // ---------------------------------------------------------------------

    public function test_regenerate_picks_up_a_slot_that_did_not_exist_at_first_generation(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 1, 'budget' => 10000]));
        $plan = TourPlan::firstOrFail();
        $this->assertNull($plan->days()->first()->suggested_availability_id);

        $newSlot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'price' => 1000]);

        $this->actingAs($traveler)
            ->post(route('tour-plans.regenerate', $plan))
            ->assertRedirect(route('tour-plans.show', $plan));

        $plan->refresh();
        $this->assertSame($newSlot->id, $plan->days()->first()->suggested_availability_id);
        $this->assertNotNull($plan->regenerated_at);
        $this->assertSame(1, $plan->days()->count());
    }

    public function test_deleting_a_plan_cascades_to_its_days(): void
    {
        $traveler = $this->traveler();
        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 3]));
        $plan = TourPlan::firstOrFail();
        $dayIds = $plan->days()->pluck('id');

        $this->actingAs($traveler)
            ->delete(route('tour-plans.destroy', $plan))
            ->assertRedirect(route('tour-plans.index'));

        $this->assertDatabaseMissing('tour_plans', ['id' => $plan->id]);
        foreach ($dayIds as $id) {
            $this->assertDatabaseMissing('tour_plan_days', ['id' => $id]);
        }
    }

    public function test_deleting_the_suggested_slot_nulls_the_reference_instead_of_erroring(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'price' => 1000]);

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 1, 'budget' => 10000]));
        $day = TourPlan::firstOrFail()->days()->firstOrFail();
        $this->assertSame($slot->id, $day->suggested_availability_id);

        $slot->delete();

        $this->assertNull($day->fresh()->suggested_availability_id);
    }

    public function test_suggestion_is_not_bookable_once_the_slot_becomes_full(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'price' => 1000, 'capacity' => 1]);

        $this->actingAs($traveler)->post(route('tour-plans.store'), $this->basePayload(['days' => 1, 'budget' => 10000]));
        $day = TourPlan::firstOrFail()->days()->firstOrFail();
        $this->assertTrue($day->suggestionIsStillBookable());

        $slot->forceFill(['booked_count' => 1])->save();

        $this->assertFalse($day->fresh()->suggestionIsStillBookable());
    }
}
