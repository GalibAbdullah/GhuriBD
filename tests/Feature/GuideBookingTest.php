<?php

namespace Tests\Feature;

use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GuideBookingTest extends TestCase
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

    private function verifiedGuide(): User
    {
        $guide = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->approved()->create([
            'user_id' => $guide->id,
            'provider_type' => ProviderType::TOUR_GUIDE->value,
        ]);

        return $guide;
    }

    private function traveler(): User
    {
        return $this->userWithRole(UserRole::TRAVELER->value);
    }

    // --- Booking flow -----------------------------------------------

    public function test_traveler_can_book_an_open_guide_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 4, 'price' => 1000]);

        $response = $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 2,
        ]);

        $response->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame($traveler->id, $booking->user_id);
        $this->assertSame($slot->id, $booking->guide_availability_id);
        $this->assertSame(2, $booking->guests);
        $this->assertEquals(2000.0, (float) $booking->total_amount);
        $this->assertSame($slot->available_date->toDateString(), $booking->travel_date->toDateString());

        $this->assertSame(2, $slot->fresh()->booked_count);
    }

    public function test_booking_a_slot_to_full_capacity_flips_its_status_to_booked(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 2, 'price' => 500]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 2,
        ]);

        $fresh = $slot->fresh();
        $this->assertSame(2, $fresh->booked_count);
        $this->assertTrue($fresh->isFullyBooked());
        $this->assertSame('Booked', $fresh->status->value);
    }

    public function test_traveler_cannot_book_more_seats_than_remain(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 2, 'price' => 500]);

        $response = $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 5,
        ]);

        $response->assertSessionHasErrors('guests');
        $this->assertSame(0, $slot->fresh()->booked_count);
    }

    public function test_traveler_cannot_book_a_fully_booked_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->booked(3)->create(['user_id' => $guide->id, 'capacity' => 3]);

        $response = $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 1,
        ]);

        $response->assertSessionHasErrors('guide_availability_id');
    }

    public function test_traveler_cannot_book_a_blocked_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->blocked()->create(['user_id' => $guide->id]);

        $response = $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 1,
        ]);

        $response->assertSessionHasErrors('guide_availability_id');
    }

    public function test_traveler_cannot_double_book_the_same_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 10, 'price' => 500]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 1,
        ]);

        $response = $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 1,
        ]);

        $response->assertSessionHasErrors('guide_availability_id');
        $this->assertSame(1, Booking::where('guide_availability_id', $slot->id)->count());
    }

    public function test_the_create_page_404s_for_a_slot_that_is_not_bookable(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->blocked()->create(['user_id' => $guide->id]);

        $this->actingAs($traveler)->get(route('bookings.guides.create', $slot))->assertNotFound();
    }

    public function test_booking_notifies_traveler_and_guide(): void
    {
        Notification::fake();

        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 4, 'price' => 500]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 1,
        ]);

        Notification::assertSentTo($traveler, \App\Notifications\BookingCreated::class);
        Notification::assertSentTo($guide, \App\Notifications\NewBookingReceived::class);
    }

    // --- Cancellation releases capacity ---------------------------------

    public function test_cancelling_a_guide_booking_releases_the_seats(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 2, 'price' => 500]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide',
            'guide_availability_id' => $slot->id,
            'guests' => 2,
        ]);

        $this->assertTrue($slot->fresh()->isFullyBooked());

        $booking = Booking::firstOrFail();
        $this->actingAs($traveler)->patch(route('traveler.bookings.cancel', $booking))->assertRedirect();

        $fresh = $slot->fresh();
        $this->assertSame(0, $fresh->booked_count);
        $this->assertSame('Available', $fresh->status->value);
    }

    // --- Partner (guide) side -----------------------------------------

    public function test_guide_sees_bookings_for_their_own_slots(): void
    {
        $guide = $this->verifiedGuide();
        $otherGuide = $this->verifiedGuide();
        $traveler = $this->traveler();

        $ownSlot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 5, 'price' => 500]);
        $otherSlot = GuideAvailability::factory()->create(['user_id' => $otherGuide->id, 'capacity' => 5, 'price' => 500]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide', 'guide_availability_id' => $ownSlot->id, 'guests' => 1,
        ]);
        $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide', 'guide_availability_id' => $otherSlot->id, 'guests' => 1,
        ]);

        $response = $this->actingAs($guide)->get(route('partner.bookings.index', ['type' => 'guide']));

        $response->assertOk();
        $ownBooking = Booking::where('guide_availability_id', $ownSlot->id)->firstOrFail();
        $otherBooking = Booking::where('guide_availability_id', $otherSlot->id)->firstOrFail();

        $response->assertSee($ownBooking->booking_reference);
        $response->assertDontSee($otherBooking->booking_reference);
    }

    public function test_guide_can_view_a_booking_for_their_own_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 5, 'price' => 500]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide', 'guide_availability_id' => $slot->id, 'guests' => 1,
        ]);
        $booking = Booking::firstOrFail();

        $this->actingAs($guide)->get(route('partner.bookings.show', $booking))->assertOk();
    }

    public function test_another_partner_cannot_view_a_guides_booking(): void
    {
        $guide = $this->verifiedGuide();
        $otherPartner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 5, 'price' => 500]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'booking_type' => 'guide', 'guide_availability_id' => $slot->id, 'guests' => 1,
        ]);
        $booking = Booking::firstOrFail();

        $this->actingAs($otherPartner)->get(route('partner.bookings.show', $booking))->assertForbidden();
    }

    // --- UI wiring -------------------------------------------------------

    public function test_traveler_sees_a_book_button_on_the_guide_profile_page(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id, 'capacity' => 5, 'price' => 500]);

        $response = $this->actingAs($traveler)->get(route('guides.show', $guide));

        $response->assertOk();
        $response->assertSee(route('bookings.guides.create', $slot), false);
    }

    public function test_non_guide_partner_does_not_see_the_availability_nav_link(): void
    {
        $resortOwner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        ProviderVerification::factory()->approved()->create([
            'user_id' => $resortOwner->id,
            'provider_type' => ProviderType::RESORT_OWNER->value,
        ]);

        $response = $this->actingAs($resortOwner)->get(route('partner.dashboard'));

        $response->assertOk();
        $response->assertDontSee(route('partner.availability.index'), false);
    }

    public function test_verified_guide_sees_the_availability_nav_link(): void
    {
        $guide = $this->verifiedGuide();

        $response = $this->actingAs($guide)->get(route('partner.dashboard'));

        $response->assertOk();
        $response->assertSee(route('partner.availability.index'), false);
    }
}
