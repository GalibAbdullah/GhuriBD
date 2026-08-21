<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\Payment;
use App\Models\ProviderVerification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
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

    private function bookableSlot(User $guide, array $overrides = []): GuideAvailability
    {
        return GuideAvailability::factory()->create(array_merge([
            'user_id' => $guide->id,
            'capacity' => 5,
            'price' => 2000,
        ], $overrides));
    }

    // ---------------------------------------------------------------------
    // Guide profile / discovery
    // ---------------------------------------------------------------------

    public function test_guide_profile_shows_only_bookable_slots(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        $open = $this->bookableSlot($guide, ['price' => 4321]);
        $blocked = GuideAvailability::factory()->blocked()->create(['user_id' => $guide->id, 'price' => 5555]);
        $past = GuideAvailability::factory()->past()->create(['user_id' => $guide->id, 'price' => 6666]);

        $response = $this->actingAs($traveler)
            ->get(route('guides.show', $guide))
            ->assertOk()
            ->assertSee($guide->name);

        $response->assertSee(number_format((float) $open->price, 2), false);
        $response->assertDontSee(number_format((float) $blocked->price, 2), false);
        $response->assertDontSee(number_format((float) $past->price, 2), false);
    }

    public function test_non_guide_profile_returns_404(): void
    {
        $traveler = $this->traveler();
        $randomPartner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($traveler)
            ->get(route('guides.show', $randomPartner))
            ->assertNotFound();
    }

    // ---------------------------------------------------------------------
    // Creating a booking
    // ---------------------------------------------------------------------

    public function test_traveler_can_book_an_available_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);

        $this->actingAs($traveler)
            ->post(route('bookings.store'), [
                'availability_id' => $slot->id,
                'party_size' => 2,
            ])
            ->assertRedirect(route('bookings.checkout', Booking::first()));

        $booking = Booking::firstOrFail();

        $this->assertSame($traveler->id, $booking->traveler_id);
        $this->assertSame(2, $booking->party_size);
        $this->assertSame('2000.00', $booking->unit_price);
        $this->assertSame('4000.00', $booking->total_price);
        $this->assertTrue($booking->status->isPendingPayment());
        $this->assertNotEmpty($booking->reference);
    }

    public function test_booking_does_not_consume_capacity_until_payment_succeeds(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide, ['capacity' => 3]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'availability_id' => $slot->id,
            'party_size' => 3,
        ]);

        $this->assertSame(0, $slot->fresh()->booked_count);
        $this->assertSame(3, $slot->fresh()->remainingCapacity());
    }

    public function test_only_travelers_can_create_bookings(): void
    {
        $guide = $this->verifiedGuide();
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $slot = $this->bookableSlot($guide);

        $this->actingAs($partner)
            ->post(route('bookings.store'), ['availability_id' => $slot->id, 'party_size' => 1])
            ->assertForbidden();
    }

    public function test_cannot_book_a_blocked_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->blocked()->create(['user_id' => $guide->id]);

        $this->actingAs($traveler)
            ->post(route('bookings.store'), ['availability_id' => $slot->id, 'party_size' => 1])
            ->assertSessionHasErrors('availability_id');
    }

    public function test_cannot_book_a_past_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->past()->create(['user_id' => $guide->id]);

        $this->actingAs($traveler)
            ->post(route('bookings.store'), ['availability_id' => $slot->id, 'party_size' => 1])
            ->assertSessionHasErrors('availability_id');
    }

    public function test_party_size_cannot_exceed_remaining_capacity(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide, ['capacity' => 2]);

        $this->actingAs($traveler)
            ->post(route('bookings.store'), ['availability_id' => $slot->id, 'party_size' => 3])
            ->assertSessionHasErrors('party_size');
    }

    public function test_party_size_must_be_at_least_one(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);

        $this->actingAs($traveler)
            ->post(route('bookings.store'), ['availability_id' => $slot->id, 'party_size' => 0])
            ->assertSessionHasErrors('party_size');
    }

    public function test_cannot_book_a_nonexistent_slot(): void
    {
        $traveler = $this->traveler();

        $this->actingAs($traveler)
            ->post(route('bookings.store'), ['availability_id' => 999999, 'party_size' => 1])
            ->assertSessionHasErrors('availability_id');
    }

    public function test_price_is_snapshotted_even_if_guide_later_edits_the_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide, ['price' => 1500]);

        $this->actingAs($traveler)->post(route('bookings.store'), [
            'availability_id' => $slot->id,
            'party_size' => 1,
        ]);

        $booking = Booking::firstOrFail();

        // Guide's slot remains editable (no confirmed booking yet) — directly
        // mutate the price to simulate an edit landing between booking and payment.
        $slot->forceFill(['price' => 9000])->save();

        $this->assertSame('1500.00', $booking->fresh()->unit_price);
    }

    // ---------------------------------------------------------------------
    // Payment — approval path
    // ---------------------------------------------------------------------

    public function test_checkout_redirects_to_mock_gateway(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->get(route('bookings.checkout', $booking))
            ->assertRedirect();

        $payment = $booking->payments()->firstOrFail();
        $this->assertSame('mock', $payment->gateway);
        $this->assertTrue($payment->status->isInitiated());
    }

    public function test_approving_mock_payment_confirms_booking_and_consumes_capacity(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide, ['capacity' => 5]);
        $booking = Booking::factory()->forSlot($slot, 2)->create(['traveler_id' => $traveler->id]);
        $payment = $booking->payments()->create([
            'gateway' => 'mock',
            'gateway_reference' => 'MOCK-TEST-1',
            'amount' => $booking->total_price,
            'currency' => 'BDT',
            'status' => PaymentStatus::INITIATED->value,
        ]);

        $this->actingAs($traveler)
            ->post(route('payments.mock.callback', $payment), ['decision' => 'approve'])
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHas('status');

        $this->assertTrue($booking->fresh()->status->isConfirmed());
        $this->assertTrue($payment->fresh()->status->isSucceeded());
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame(2, $slot->fresh()->booked_count);
    }

    public function test_declining_mock_payment_leaves_booking_pending_for_retry(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);
        $payment = $booking->payments()->create([
            'gateway' => 'mock', 'gateway_reference' => 'MOCK-TEST-2',
            'amount' => $booking->total_price, 'currency' => 'BDT',
            'status' => PaymentStatus::INITIATED->value,
        ]);

        $this->actingAs($traveler)
            ->post(route('payments.mock.callback', $payment), ['decision' => 'decline'])
            ->assertRedirect(route('bookings.show', $booking))
            ->assertSessionHasErrors('payment');

        $this->assertTrue($booking->fresh()->status->isPendingPayment());
        $this->assertTrue($payment->fresh()->status->isFailed());
        $this->assertSame(0, $slot->fresh()->booked_count);

        // Retry is still possible.
        $this->assertTrue($booking->fresh()->canBePaid());
    }

    public function test_a_resolved_payment_cannot_be_processed_twice(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide, ['capacity' => 5]);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);
        $payment = $booking->payments()->create([
            'gateway' => 'mock', 'gateway_reference' => 'MOCK-TEST-3',
            'amount' => $booking->total_price, 'currency' => 'BDT',
            'status' => PaymentStatus::INITIATED->value,
        ]);

        $this->actingAs($traveler)->post(route('payments.mock.callback', $payment), ['decision' => 'approve']);
        $this->assertSame(1, $slot->fresh()->booked_count);

        // Replayed/double-submitted callback must not double-charge capacity.
        $this->actingAs($traveler)
            ->post(route('payments.mock.callback', $payment), ['decision' => 'approve'])
            ->assertSessionHas('status', 'This payment has already been processed.');

        $this->assertSame(1, $slot->fresh()->booked_count);
    }

    public function test_only_the_owning_traveler_can_pay_or_view_the_mock_checkout(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $otherTraveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);
        $payment = $booking->payments()->create([
            'gateway' => 'mock', 'gateway_reference' => 'MOCK-TEST-4',
            'amount' => $booking->total_price, 'currency' => 'BDT',
            'status' => PaymentStatus::INITIATED->value,
        ]);

        $this->actingAs($otherTraveler)->get(route('payments.mock.show', $payment))->assertForbidden();
        $this->actingAs($otherTraveler)
            ->post(route('payments.mock.callback', $payment), ['decision' => 'approve'])
            ->assertForbidden();

        $this->assertTrue($payment->fresh()->status->isInitiated());
    }

    // ---------------------------------------------------------------------
    // The overselling race
    // ---------------------------------------------------------------------

    public function test_last_seat_race_only_confirms_one_payment(): void
    {
        $guide = $this->verifiedGuide();
        $slot = $this->bookableSlot($guide, ['capacity' => 1]);

        $travelerA = $this->traveler();
        $travelerB = $this->traveler();

        $bookingA = Booking::factory()->forSlot($slot)->create(['traveler_id' => $travelerA->id]);
        $bookingB = Booking::factory()->forSlot($slot)->create(['traveler_id' => $travelerB->id]);

        // Both bookings were allowed to reach checkout — capacity is only
        // authoritative at payment time, not at booking-request time.
        $paymentA = $bookingA->payments()->create([
            'gateway' => 'mock', 'gateway_reference' => 'MOCK-RACE-A',
            'amount' => $bookingA->total_price, 'currency' => 'BDT',
            'status' => PaymentStatus::INITIATED->value,
        ]);
        $paymentB = $bookingB->payments()->create([
            'gateway' => 'mock', 'gateway_reference' => 'MOCK-RACE-B',
            'amount' => $bookingB->total_price, 'currency' => 'BDT',
            'status' => PaymentStatus::INITIATED->value,
        ]);

        $this->actingAs($travelerA)
            ->post(route('payments.mock.callback', $paymentA), ['decision' => 'approve'])
            ->assertSessionHas('status');

        $this->actingAs($travelerB)
            ->post(route('payments.mock.callback', $paymentB), ['decision' => 'approve'])
            ->assertSessionHasErrors('payment');

        $this->assertTrue($bookingA->fresh()->status->isConfirmed());
        $this->assertTrue($bookingB->fresh()->status->isCancelled());

        // Traveler B's charge is modelled as captured-then-refunded, not simply
        // "failed" — the gateway really did take the money for a moment.
        $this->assertTrue($paymentB->fresh()->status->isRefunded());
        $this->assertTrue($paymentA->fresh()->status->isSucceeded());

        $this->assertSame(1, $slot->fresh()->booked_count);
    }

    public function test_deleting_a_slot_cancels_its_unpaid_pending_bookings(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $slot->delete();

        $this->assertTrue($booking->fresh()->isCancelled());
    }

    // ---------------------------------------------------------------------
    // Cancellation & refund
    // ---------------------------------------------------------------------

    public function test_traveler_can_cancel_a_pending_booking(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->post(route('bookings.cancel', $booking), ['reason' => 'Changed my mind'])
            ->assertRedirect(route('bookings.show', $booking));

        $booking->refresh();
        $this->assertTrue($booking->isCancelled());
        $this->assertSame('Changed my mind', $booking->cancellation_reason);
    }

    public function test_cancelling_a_confirmed_booking_releases_capacity_and_refunds(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        // Explicitly well outside the 24-hour cancellation window — the
        // factory's random date could otherwise occasionally land inside it
        // and make this assertion flaky.
        $slot = $this->bookableSlot($guide, [
            'capacity' => 5,
            'available_date' => GuideAvailability::today()->addDays(5)->toDateString(),
        ]);
        $booking = Booking::factory()->forSlot($slot, 2)->confirmed()->create(['traveler_id' => $traveler->id]);
        $slot->forceFill(['booked_count' => 2])->save();

        Payment::factory()->succeeded()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_price,
        ]);

        $this->actingAs($traveler)
            ->post(route('bookings.cancel', $booking))
            ->assertSessionHas('status');

        $this->assertTrue($booking->fresh()->isCancelled());
        $this->assertSame(0, $slot->fresh()->booked_count);
        $this->assertTrue($booking->payments()->latest()->first()->status->isRefunded());
    }

    public function test_confirmed_booking_cannot_be_cancelled_within_the_cancellation_window(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        // Starts in 2 hours — inside the default 24-hour cancellation window.
        $startsAt = now(GuideAvailability::timezone())->addHours(2);
        $slot = GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'available_date' => $startsAt->toDateString(),
            'start_time' => $startsAt->format('H:i:s'),
            'end_time' => $startsAt->copy()->addHours(2)->format('H:i:s'),
            'capacity' => 5,
        ]);

        $booking = Booking::factory()->forSlot($slot)->confirmed()->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->post(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertTrue($booking->fresh()->isConfirmed());
    }

    public function test_pending_booking_can_always_be_cancelled_regardless_of_window(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        $startsAt = now(GuideAvailability::timezone())->addHours(2);
        $slot = GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'available_date' => $startsAt->toDateString(),
            'start_time' => $startsAt->format('H:i:s'),
            'end_time' => $startsAt->copy()->addHours(2)->format('H:i:s'),
        ]);

        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->post(route('bookings.cancel', $booking))
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertTrue($booking->fresh()->isCancelled());
    }

    public function test_cannot_cancel_an_already_cancelled_booking(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->cancelled()->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->post(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    public function test_cannot_cancel_a_booking_whose_slot_already_started(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = GuideAvailability::factory()->past()->create(['user_id' => $guide->id]);
        $booking = Booking::factory()->forSlot($slot)->confirmed()->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->post(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    public function test_only_the_owning_traveler_can_cancel(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $otherTraveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($otherTraveler)
            ->post(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------------
    // Access control & viewing
    // ---------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('bookings.index'))->assertRedirect('/login');
    }

    public function test_traveler_can_view_own_booking(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee($booking->reference);
    }

    public function test_guide_can_view_a_booking_made_against_their_slot(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($guide)
            ->get(route('bookings.show', $booking))
            ->assertOk();
    }

    public function test_unrelated_user_cannot_view_a_booking(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $stranger = $this->traveler();
        $slot = $this->bookableSlot($guide);
        $booking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($stranger)
            ->get(route('bookings.show', $booking))
            ->assertForbidden();
    }

    public function test_booking_index_only_lists_the_current_travelers_bookings(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $other = $this->traveler();
        $slot = $this->bookableSlot($guide);

        $mine = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);
        Booking::factory()->forSlot($slot)->create(['traveler_id' => $other->id]);

        $this->actingAs($traveler)
            ->get(route('bookings.index', ['scope' => 'all']))
            ->assertOk()
            ->assertSee($mine->reference);

        $this->assertSame(1, Booking::where('traveler_id', $traveler->id)->count());
    }

    public function test_booking_index_upcoming_scope_excludes_past_slots(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();
        $pastSlot = GuideAvailability::factory()->past()->create(['user_id' => $guide->id]);

        $pastBooking = Booking::factory()->forSlot($pastSlot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->get(route('bookings.index', ['scope' => 'upcoming']))
            ->assertOk()
            ->assertDontSee($pastBooking->reference);

        $this->actingAs($traveler)
            ->get(route('bookings.index', ['scope' => 'past']))
            ->assertOk()
            ->assertSee($pastBooking->reference);
    }

    // ---------------------------------------------------------------------
    // Guide-side bookings list
    // ---------------------------------------------------------------------

    public function test_guide_sees_bookings_against_their_own_slots_only(): void
    {
        $guide = $this->verifiedGuide();
        $otherGuide = $this->verifiedGuide();
        $traveler = $this->traveler();

        $mySlot = $this->bookableSlot($guide);
        $otherSlot = $this->bookableSlot($otherGuide);

        $mine = Booking::factory()->forSlot($mySlot)->create(['traveler_id' => $traveler->id]);
        $notMine = Booking::factory()->forSlot($otherSlot)->create(['traveler_id' => $traveler->id]);

        $this->actingAs($guide)
            ->get(route('partner.bookings.index'))
            ->assertOk()
            ->assertSee($mine->reference)
            ->assertDontSee($notMine->reference);
    }

    public function test_unverified_partner_cannot_view_bookings_list(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->get(route('partner.bookings.index'))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------------
    // Model behaviour
    // ---------------------------------------------------------------------

    public function test_reference_codes_are_unique(): void
    {
        $refs = collect(range(1, 20))->map(fn () => Booking::generateReference());

        $this->assertSame($refs->count(), $refs->unique()->count());
    }

    public function test_is_expired_is_true_only_for_pending_bookings_past_start(): void
    {
        $pastSlot = GuideAvailability::factory()->past()->make();
        $upcomingSlot = GuideAvailability::factory()->make([
            'available_date' => GuideAvailability::today()->addDay()->toDateString(),
        ]);

        $expired = Booking::factory()->make(['status' => BookingStatus::PENDING_PAYMENT->value]);
        $expired->setRelation('bookable', $pastSlot);

        $notExpired = Booking::factory()->make(['status' => BookingStatus::PENDING_PAYMENT->value]);
        $notExpired->setRelation('bookable', $upcomingSlot);

        $this->assertTrue($expired->isExpired());
        $this->assertFalse($notExpired->isExpired());
    }
}
