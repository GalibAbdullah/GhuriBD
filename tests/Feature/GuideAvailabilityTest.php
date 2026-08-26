<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuideAvailabilityTest extends TestCase
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

    /**
     * A Travel Partner holding an approved Tour Guide verification.
     */
    private function verifiedGuide(): User
    {
        $guide = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->approved()->create([
            'user_id' => $guide->id,
            'provider_type' => ProviderType::TOUR_GUIDE->value,
        ]);

        return $guide;
    }

    private function tomorrow(): string
    {
        return GuideAvailability::today()->addDay()->toDateString();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'available_date' => $this->tomorrow(),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'capacity' => 5,
            'price' => 3500,
            'status' => AvailabilityStatus::AVAILABLE->value,
            'notes' => 'Old Dhaka heritage walk.',
        ], $overrides);
    }

    // ---------------------------------------------------------------------
    // Access control
    // ---------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('partner.availability.index'))->assertRedirect('/login');
        $this->get(route('partner.availability.create'))->assertRedirect('/login');
        $this->post(route('partner.availability.store'), [])->assertRedirect('/login');
    }

    public function test_traveler_cannot_access_availability(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)->get(route('partner.availability.index'))->assertForbidden();
        $this->actingAs($traveler)->post(route('partner.availability.store'), $this->payload())->assertForbidden();
    }

    public function test_unverified_partner_cannot_manage_availability(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)->get(route('partner.availability.index'))->assertForbidden();
        $this->actingAs($partner)->post(route('partner.availability.store'), $this->payload())->assertForbidden();
    }

    public function test_partner_with_pending_guide_verification_cannot_manage_availability(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
            'provider_type' => ProviderType::TOUR_GUIDE->value,
        ]);

        $this->actingAs($partner)->get(route('partner.availability.index'))->assertForbidden();
    }

    public function test_partner_verified_as_resort_owner_cannot_manage_guide_availability(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->approved()->create([
            'user_id' => $partner->id,
            'provider_type' => ProviderType::RESORT_OWNER->value,
        ]);

        $this->assertTrue($partner->isVerifiedProvider());
        $this->assertFalse($partner->isVerifiedTourGuide());

        $this->actingAs($partner)->get(route('partner.availability.index'))->assertForbidden();
    }

    public function test_verified_guide_can_view_calendar(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->get(route('partner.availability.index'))
            ->assertOk()
            ->assertSee('Guide Availability');
    }

    // ---------------------------------------------------------------------
    // Creating slots
    // ---------------------------------------------------------------------

    public function test_guide_can_create_a_slot(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload())
            ->assertRedirect(route('partner.availability.index'))
            ->assertSessionHas('status');

        $slot = $guide->guideAvailabilities()->firstOrFail();

        $this->assertSame($this->tomorrow(), $slot->available_date->toDateString());
        $this->assertSame('09:00:00', $slot->start_time);
        $this->assertSame('12:00:00', $slot->end_time);
        $this->assertSame(5, $slot->capacity);
        $this->assertTrue($slot->status->isAvailable());
    }

    public function test_new_slot_starts_with_no_bookings(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)->post(route('partner.availability.store'), $this->payload());

        $this->assertSame(0, $guide->guideAvailabilities()->firstOrFail()->booked_count);
    }

    public function test_booked_count_cannot_be_mass_assigned(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload(['booked_count' => 99]));

        $this->assertSame(0, $guide->guideAvailabilities()->firstOrFail()->booked_count);
    }

    public function test_guide_cannot_set_system_managed_booked_status(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'status' => AvailabilityStatus::BOOKED->value,
            ]))
            ->assertSessionHasErrors('status');
    }

    public function test_slot_creation_requires_all_fields(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), [])
            ->assertSessionHasErrors([
                'available_date',
                'start_time',
                'end_time',
                'capacity',
                'price',
                'status',
            ]);
    }

    public function test_slot_cannot_be_created_in_the_past(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'available_date' => GuideAvailability::today()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('available_date');

        $this->assertDatabaseCount('guide_availabilities', 0);
    }

    public function test_slot_cannot_be_created_beyond_the_advance_window(): void
    {
        $guide = $this->verifiedGuide();
        $tooFar = GuideAvailability::today()
            ->addDays(config('ghuribd.availability.max_advance_days') + 1)
            ->toDateString();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload(['available_date' => $tooFar]))
            ->assertSessionHasErrors('available_date');
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'start_time' => '14:00',
                'end_time' => '10:00',
            ]))
            ->assertSessionHasErrors('end_time');
    }

    public function test_end_time_equal_to_start_time_is_rejected(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'start_time' => '10:00',
                'end_time' => '10:00',
            ]))
            ->assertSessionHasErrors('end_time');
    }

    public function test_slot_shorter_than_the_minimum_duration_is_rejected(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'start_time' => '10:00',
                'end_time' => '10:10',
            ]))
            ->assertSessionHasErrors('end_time');
    }

    public function test_malformed_time_is_rejected_without_erroring(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'start_time' => 'not-a-time',
                'end_time' => '25:99',
            ]))
            ->assertSessionHasErrors(['start_time', 'end_time']);
    }

    public function test_slot_earlier_today_is_rejected(): void
    {
        $guide = $this->verifiedGuide();

        // Freeze at 14:00 Dhaka time, then try to publish a 09:00 slot for today.
        Carbon::setTestNow(Carbon::parse('14:00', GuideAvailability::timezone()));

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'available_date' => GuideAvailability::today()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '11:00',
            ]))
            ->assertSessionHasErrors('start_time');

        Carbon::setTestNow();
    }

    public function test_slot_later_today_is_accepted(): void
    {
        $guide = $this->verifiedGuide();

        Carbon::setTestNow(Carbon::parse('08:00', GuideAvailability::timezone()));

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'available_date' => GuideAvailability::today()->toDateString(),
                'start_time' => '17:00',
                'end_time' => '19:00',
            ]))
            ->assertSessionHasNoErrors();

        Carbon::setTestNow();
    }

    public function test_capacity_must_be_within_bounds(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload(['capacity' => 0]))
            ->assertSessionHasErrors('capacity');

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'capacity' => config('ghuribd.availability.max_capacity') + 1,
            ]))
            ->assertSessionHasErrors('capacity');
    }

    public function test_price_cannot_be_negative(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload(['price' => -1]))
            ->assertSessionHasErrors('price');
    }

    public function test_price_accepts_thousands_separators(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload(['price' => '3,500.50']))
            ->assertSessionHasNoErrors();

        $this->assertSame('3500.50', $guide->guideAvailabilities()->firstOrFail()->price);
    }

    public function test_time_without_leading_zero_is_normalised(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'start_time' => '9:05',
                'end_time' => '11:30',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('guide_availabilities', [
            'user_id' => $guide->id,
            'start_time' => '09:05:00',
        ]);
    }

    // ---------------------------------------------------------------------
    // Overlap rules
    // ---------------------------------------------------------------------

    public function test_overlapping_slot_on_same_date_is_rejected(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()
            ->onDate($this->tomorrow())
            ->between('09:00', '12:00')
            ->create(['user_id' => $guide->id]);

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'start_time' => '11:00',
                'end_time' => '14:00',
            ]))
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('guide_availabilities', 1);
    }

    public function test_slot_fully_containing_an_existing_slot_is_rejected(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()
            ->onDate($this->tomorrow())
            ->between('10:00', '11:00')
            ->create(['user_id' => $guide->id]);

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'start_time' => '09:00',
                'end_time' => '13:00',
            ]))
            ->assertSessionHasErrors('start_time');
    }

    public function test_adjacent_slots_are_allowed(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()
            ->onDate($this->tomorrow())
            ->between('09:00', '12:00')
            ->create(['user_id' => $guide->id]);

        // Starts exactly when the other ends — touching, not overlapping.
        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'start_time' => '12:00',
                'end_time' => '15:00',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('guide_availabilities', 2);
    }

    public function test_same_times_on_a_different_date_are_allowed(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()
            ->onDate($this->tomorrow())
            ->between('09:00', '12:00')
            ->create(['user_id' => $guide->id]);

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload([
                'available_date' => GuideAvailability::today()->addDays(2)->toDateString(),
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_another_guides_slot_does_not_block_this_guide(): void
    {
        $guide = $this->verifiedGuide();
        $otherGuide = $this->verifiedGuide();

        GuideAvailability::factory()
            ->onDate($this->tomorrow())
            ->between('09:00', '12:00')
            ->create(['user_id' => $otherGuide->id]);

        $this->actingAs($guide)
            ->post(route('partner.availability.store'), $this->payload())
            ->assertSessionHasNoErrors();
    }

    // ---------------------------------------------------------------------
    // Updating and deleting
    // ---------------------------------------------------------------------

    public function test_guide_can_update_own_slot(): void
    {
        $guide = $this->verifiedGuide();
        $slot = GuideAvailability::factory()->onDate($this->tomorrow())->between('09:00', '12:00')
            ->create(['user_id' => $guide->id]);

        $this->actingAs($guide)
            ->put(route('partner.availability.update', $slot), $this->payload([
                'start_time' => '13:00',
                'end_time' => '16:00',
                'capacity' => 8,
            ]))
            ->assertRedirect(route('partner.availability.index'));

        $slot->refresh();

        $this->assertSame('13:00:00', $slot->start_time);
        $this->assertSame(8, $slot->capacity);
    }

    public function test_updating_a_slot_does_not_conflict_with_itself(): void
    {
        $guide = $this->verifiedGuide();
        $slot = GuideAvailability::factory()->onDate($this->tomorrow())->between('09:00', '12:00')
            ->create(['user_id' => $guide->id]);

        // Same window, only the price changes.
        $this->actingAs($guide)
            ->put(route('partner.availability.update', $slot), $this->payload([
                'start_time' => '09:00',
                'end_time' => '12:00',
                'price' => 4200,
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_updating_into_another_slots_window_is_rejected(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()->onDate($this->tomorrow())->between('14:00', '17:00')
            ->create(['user_id' => $guide->id]);

        $slot = GuideAvailability::factory()->onDate($this->tomorrow())->between('09:00', '12:00')
            ->create(['user_id' => $guide->id]);

        $this->actingAs($guide)
            ->put(route('partner.availability.update', $slot), $this->payload([
                'start_time' => '15:00',
                'end_time' => '16:00',
            ]))
            ->assertSessionHasErrors('start_time');
    }

    public function test_guide_cannot_update_another_guides_slot(): void
    {
        $guide = $this->verifiedGuide();
        $otherGuide = $this->verifiedGuide();

        $slot = GuideAvailability::factory()->create(['user_id' => $otherGuide->id]);

        $this->actingAs($guide)->get(route('partner.availability.edit', $slot))->assertForbidden();
        $this->actingAs($guide)->put(route('partner.availability.update', $slot), $this->payload())->assertForbidden();
        $this->actingAs($guide)->delete(route('partner.availability.destroy', $slot))->assertForbidden();
    }

    public function test_slot_with_bookings_cannot_be_edited_or_deleted(): void
    {
        $guide = $this->verifiedGuide();
        $slot = GuideAvailability::factory()->booked(2)->create(['user_id' => $guide->id]);

        $this->actingAs($guide)->get(route('partner.availability.edit', $slot))->assertForbidden();
        $this->actingAs($guide)->put(route('partner.availability.update', $slot), $this->payload())->assertForbidden();
        $this->actingAs($guide)->delete(route('partner.availability.destroy', $slot))->assertForbidden();

        $this->assertDatabaseHas('guide_availabilities', ['id' => $slot->id]);
    }

    public function test_past_slot_cannot_be_edited_or_deleted(): void
    {
        $guide = $this->verifiedGuide();
        $slot = GuideAvailability::factory()->past()->create(['user_id' => $guide->id]);

        $this->actingAs($guide)->get(route('partner.availability.edit', $slot))->assertForbidden();
        $this->actingAs($guide)->delete(route('partner.availability.destroy', $slot))->assertForbidden();
    }

    public function test_booked_slot_is_rejected_before_validation_runs(): void
    {
        $guide = $this->verifiedGuide();

        $slot = GuideAvailability::factory()->onDate($this->tomorrow())->between('09:00', '12:00')
            ->create(['user_id' => $guide->id, 'capacity' => 10]);

        $slot->forceFill(['booked_count' => 4])->save();

        // Deliberately invalid payload: the policy must answer 403 rather than
        // leaking field-level validation errors for a slot the guide cannot touch.
        $this->actingAs($guide)
            ->put(route('partner.availability.update', $slot), $this->payload([
                'capacity' => 0,
                'price' => -5,
            ]))
            ->assertForbidden()
            ->assertSessionHasNoErrors();

        $this->assertSame(10, $slot->fresh()->capacity);
    }

    public function test_guide_can_delete_own_unbooked_slot(): void
    {
        $guide = $this->verifiedGuide();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id]);

        $this->actingAs($guide)
            ->delete(route('partner.availability.destroy', $slot))
            ->assertRedirect(route('partner.availability.index'));

        $this->assertDatabaseMissing('guide_availabilities', ['id' => $slot->id]);
    }

    public function test_guide_can_toggle_slot_between_available_and_blocked(): void
    {
        $guide = $this->verifiedGuide();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id]);

        $this->actingAs($guide)->patch(route('partner.availability.toggle', $slot));
        $this->assertTrue($slot->fresh()->status->isBlocked());

        $this->actingAs($guide)->patch(route('partner.availability.toggle', $slot));
        $this->assertTrue($slot->fresh()->status->isAvailable());
    }

    // ---------------------------------------------------------------------
    // Bulk publishing
    // ---------------------------------------------------------------------

    public function test_bulk_publish_creates_a_slot_per_matching_weekday(): void
    {
        $guide = $this->verifiedGuide();

        // A fixed Monday keeps the weekday maths deterministic.
        Carbon::setTestNow(Carbon::parse('2026-09-07 06:00', GuideAvailability::timezone()));

        $this->actingAs($guide)
            ->post(route('partner.availability.bulk.store'), [
                'start_date' => '2026-09-07',
                'end_date' => '2026-09-20',
                'weekdays' => [1], // Mondays
                'start_time' => '09:00',
                'end_time' => '12:00',
                'capacity' => 4,
                'price' => 3000,
                'status' => AvailabilityStatus::AVAILABLE->value,
            ])
            ->assertRedirect(route('partner.availability.index'));

        // Sep 7 and Sep 14 are the Mondays in that range.
        $this->assertSame(2, $guide->guideAvailabilities()->count());

        Carbon::setTestNow();
    }

    public function test_bulk_publish_skips_dates_that_already_have_a_conflicting_slot(): void
    {
        $guide = $this->verifiedGuide();

        Carbon::setTestNow(Carbon::parse('2026-09-07 06:00', GuideAvailability::timezone()));

        GuideAvailability::factory()->onDate('2026-09-14')->between('10:00', '13:00')
            ->create(['user_id' => $guide->id]);

        $this->actingAs($guide)
            ->post(route('partner.availability.bulk.store'), [
                'start_date' => '2026-09-07',
                'end_date' => '2026-09-20',
                'weekdays' => [1],
                'start_time' => '09:00',
                'end_time' => '12:00',
                'capacity' => 4,
                'price' => 3000,
                'status' => AvailabilityStatus::AVAILABLE->value,
            ])
            ->assertSessionHas('status');

        // The pre-existing Sep 14 slot plus one new slot on Sep 7.
        $this->assertSame(2, $guide->guideAvailabilities()->count());
        $this->assertSame(1, $guide->guideAvailabilities()->where('start_time', '09:00:00')->count());

        Carbon::setTestNow();
    }

    public function test_bulk_publish_rejects_a_range_with_no_matching_weekdays(): void
    {
        $guide = $this->verifiedGuide();

        Carbon::setTestNow(Carbon::parse('2026-09-08 06:00', GuideAvailability::timezone()));

        // Tue 8th to Thu 10th contains no Sunday.
        $this->actingAs($guide)
            ->post(route('partner.availability.bulk.store'), [
                'start_date' => '2026-09-08',
                'end_date' => '2026-09-10',
                'weekdays' => [0],
                'start_time' => '09:00',
                'end_time' => '12:00',
                'capacity' => 4,
                'price' => 3000,
                'status' => AvailabilityStatus::AVAILABLE->value,
            ])
            ->assertSessionHasErrors('weekdays');

        $this->assertDatabaseCount('guide_availabilities', 0);

        Carbon::setTestNow();
    }

    public function test_bulk_publish_rejects_an_over_long_range(): void
    {
        $guide = $this->verifiedGuide();
        $today = GuideAvailability::today();

        $this->actingAs($guide)
            ->post(route('partner.availability.bulk.store'), [
                'start_date' => $today->toDateString(),
                'end_date' => $today->copy()
                    ->addDays(config('ghuribd.availability.max_bulk_range_days') + 5)
                    ->toDateString(),
                'weekdays' => [0, 1, 2, 3, 4, 5, 6],
                'start_time' => '09:00',
                'end_time' => '12:00',
                'capacity' => 4,
                'price' => 3000,
                'status' => AvailabilityStatus::AVAILABLE->value,
            ])
            ->assertSessionHasErrors('end_date');
    }

    public function test_bulk_publish_rejects_end_date_before_start_date(): void
    {
        $guide = $this->verifiedGuide();
        $today = GuideAvailability::today();

        $this->actingAs($guide)
            ->post(route('partner.availability.bulk.store'), [
                'start_date' => $today->copy()->addDays(10)->toDateString(),
                'end_date' => $today->copy()->addDays(3)->toDateString(),
                'weekdays' => [1],
                'start_time' => '09:00',
                'end_time' => '12:00',
                'capacity' => 4,
                'price' => 3000,
                'status' => AvailabilityStatus::AVAILABLE->value,
            ])
            ->assertSessionHasErrors('end_date');
    }

    public function test_bulk_publish_requires_at_least_one_weekday(): void
    {
        $guide = $this->verifiedGuide();
        $today = GuideAvailability::today();

        $this->actingAs($guide)
            ->post(route('partner.availability.bulk.store'), [
                'start_date' => $today->copy()->addDay()->toDateString(),
                'end_date' => $today->copy()->addDays(7)->toDateString(),
                'start_time' => '09:00',
                'end_time' => '12:00',
                'capacity' => 4,
                'price' => 3000,
                'status' => AvailabilityStatus::AVAILABLE->value,
            ])
            ->assertSessionHasErrors('weekdays');
    }

    public function test_unverified_partner_cannot_bulk_publish(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $today = GuideAvailability::today();

        $this->actingAs($partner)
            ->post(route('partner.availability.bulk.store'), [
                'start_date' => $today->copy()->addDay()->toDateString(),
                'end_date' => $today->copy()->addDays(7)->toDateString(),
                'weekdays' => [1],
                'start_time' => '09:00',
                'end_time' => '12:00',
                'capacity' => 4,
                'price' => 3000,
                'status' => AvailabilityStatus::AVAILABLE->value,
            ])
            ->assertForbidden();
    }

    // ---------------------------------------------------------------------
    // Listing and filtering
    // ---------------------------------------------------------------------

    public function test_calendar_only_shows_the_signed_in_guides_slots(): void
    {
        $guide = $this->verifiedGuide();
        $otherGuide = $this->verifiedGuide();

        GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'notes' => 'My own slot',
        ]);

        GuideAvailability::factory()->create([
            'user_id' => $otherGuide->id,
            'notes' => 'Someone elses slot',
        ]);

        $this->actingAs($guide)
            ->get(route('partner.availability.index', ['scope' => 'all']))
            ->assertOk()
            ->assertSee('৳', false)
            ->assertDontSee('Someone elses slot');
    }

    public function test_upcoming_scope_hides_past_slots(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()->past()->create([
            'user_id' => $guide->id,
            'capacity' => 47,
        ]);

        $this->actingAs($guide)
            ->get(route('partner.availability.index', ['scope' => 'upcoming']))
            ->assertOk()
            ->assertSee('No availability yet');
    }

    public function test_past_scope_shows_past_slots(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()->past()->create([
            'user_id' => $guide->id,
            'capacity' => 47,
        ]);

        $this->actingAs($guide)
            ->get(route('partner.availability.index', ['scope' => 'past']))
            ->assertOk()
            ->assertSee('47');
    }

    public function test_status_filter_narrows_the_list(): void
    {
        $guide = $this->verifiedGuide();

        GuideAvailability::factory()->onDate($this->tomorrow())->between('09:00', '11:00')
            ->create(['user_id' => $guide->id, 'capacity' => 31]);

        GuideAvailability::factory()->blocked()->onDate($this->tomorrow())->between('14:00', '16:00')
            ->create(['user_id' => $guide->id, 'capacity' => 42]);

        $this->actingAs($guide)
            ->get(route('partner.availability.index', ['status' => AvailabilityStatus::BLOCKED->value]))
            ->assertOk()
            ->assertSee('42')
            ->assertDontSee('31');
    }

    public function test_invalid_filter_values_fall_back_to_defaults(): void
    {
        $guide = $this->verifiedGuide();

        $this->actingAs($guide)
            ->get(route('partner.availability.index', [
                'scope' => 'garbage',
                'status' => "'; DROP TABLE guide_availabilities; --",
            ]))
            ->assertOk();

        $this->assertTrue(Schema::hasTable('guide_availabilities'));
    }

    // ---------------------------------------------------------------------
    // Model behaviour
    // ---------------------------------------------------------------------

    public function test_remaining_capacity_and_full_booking_state(): void
    {
        $slot = GuideAvailability::factory()->make([
            'capacity' => 5,
            'booked_count' => 3,
        ]);

        $this->assertSame(2, $slot->remainingCapacity());
        $this->assertFalse($slot->isFullyBooked());

        $slot->booked_count = 5;

        $this->assertSame(0, $slot->remainingCapacity());
        $this->assertTrue($slot->isFullyBooked());
    }

    public function test_blocked_slot_is_not_bookable(): void
    {
        $slot = GuideAvailability::factory()->blocked()->create();

        $this->assertFalse($slot->isBookable());
    }

    public function test_fully_booked_slot_is_not_bookable(): void
    {
        $slot = GuideAvailability::factory()->create(['capacity' => 2]);
        $slot->forceFill(['booked_count' => 2])->save();

        $this->assertFalse($slot->fresh()->isBookable());
    }

    public function test_past_slot_is_not_bookable(): void
    {
        $slot = GuideAvailability::factory()->past()->create();

        $this->assertTrue($slot->hasEnded());
        $this->assertFalse($slot->isBookable());
    }

    public function test_upcoming_available_slot_is_bookable(): void
    {
        $slot = GuideAvailability::factory()->create(['capacity' => 3]);

        $this->assertTrue($slot->isBookable());
    }

    public function test_duration_is_calculated_in_minutes(): void
    {
        $slot = GuideAvailability::factory()
            ->onDate($this->tomorrow())
            ->between('09:00', '12:30')
            ->create();

        $this->assertSame(210, $slot->durationMinutes());
    }

    public function test_deleting_a_guide_removes_their_availability(): void
    {
        $guide = $this->verifiedGuide();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id]);

        $guide->delete();

        $this->assertDatabaseMissing('guide_availabilities', ['id' => $slot->id]);
    }
}
