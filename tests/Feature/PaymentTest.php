<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingPaid;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function traveler(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::TRAVELER->value);

        return $user;
    }

    private function pendingBookingFor(User $user): Booking
    {
        return Booking::factory()->create([
            'user_id' => $user->id,
            'booking_status' => BookingStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);
    }

    public function test_checkout_starts_a_payment_attempt_and_redirects_to_the_gateway(): void
    {
        $traveler = $this->traveler();
        $booking = $this->pendingBookingFor($traveler);

        $response = $this->actingAs($traveler)->post(route('payments.checkout', $booking));

        $payment = $booking->payments()->firstOrFail();

        $response->assertRedirect(route('payments.show', $payment));
        $this->assertEquals(PaymentStatus::PENDING->value, $payment->status->value);
        $this->assertEquals((string) $booking->total_amount, (string) $payment->amount);
    }

    public function test_checkout_reuses_an_unresolved_payment_attempt(): void
    {
        $traveler = $this->traveler();
        $booking = $this->pendingBookingFor($traveler);

        $this->actingAs($traveler)->post(route('payments.checkout', $booking));
        $this->actingAs($traveler)->post(route('payments.checkout', $booking));

        $this->assertEquals(1, $booking->payments()->count());
    }

    public function test_approving_payment_confirms_the_booking(): void
    {
        Notification::fake();

        $traveler = $this->traveler();
        $booking = $this->pendingBookingFor($traveler);
        $this->actingAs($traveler)->post(route('payments.checkout', $booking));
        $payment = $booking->payments()->firstOrFail();

        $response = $this->actingAs($traveler)
            ->post(route('payments.callback', $payment), ['decision' => 'approve']);

        $response->assertRedirect(route('traveler.bookings.show', $booking));

        $booking->refresh();
        $payment->refresh();

        $this->assertEquals(BookingStatus::CONFIRMED->value, $booking->booking_status);
        $this->assertEquals(PaymentStatus::PAID->value, $booking->payment_status);
        $this->assertEquals(PaymentStatus::PAID, $payment->status);
        $this->assertNotNull($payment->paid_at);

        Notification::assertSentTo($traveler, BookingPaid::class);
    }

    public function test_a_paid_package_booking_notifies_the_partner(): void
    {
        Notification::fake();

        $traveler = $this->traveler();
        $booking = Booking::factory()->create([
            'user_id' => $traveler->id,
            'booking_status' => BookingStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);
        $partner = $booking->tourPackage->user;

        $this->actingAs($traveler)->post(route('payments.checkout', $booking));
        $payment = $booking->payments()->firstOrFail();
        $this->actingAs($traveler)->post(route('payments.callback', $payment), ['decision' => 'approve']);

        Notification::assertSentTo($partner, BookingPaid::class, function (BookingPaid $n): bool {
            return $n->audience === 'partner';
        });
    }

    public function test_declining_payment_leaves_the_booking_pending(): void
    {
        $traveler = $this->traveler();
        $booking = $this->pendingBookingFor($traveler);
        $this->actingAs($traveler)->post(route('payments.checkout', $booking));
        $payment = $booking->payments()->firstOrFail();

        $this->actingAs($traveler)
            ->post(route('payments.callback', $payment), ['decision' => 'decline'])
            ->assertSessionHasErrors('payment');

        $booking->refresh();
        $payment->refresh();

        $this->assertEquals(BookingStatus::PENDING->value, $booking->booking_status);
        $this->assertEquals(PaymentStatus::PENDING->value, $booking->payment_status);
        $this->assertEquals(PaymentStatus::FAILED, $payment->status);
    }

    public function test_a_traveler_cannot_pay_another_travelers_booking(): void
    {
        $owner = $this->traveler();
        $stranger = $this->traveler();
        $booking = $this->pendingBookingFor($owner);
        $this->actingAs($owner)->post(route('payments.checkout', $booking));
        $payment = $booking->payments()->firstOrFail();

        $this->actingAs($stranger)
            ->post(route('payments.callback', $payment), ['decision' => 'approve'])
            ->assertForbidden();

        $this->assertEquals(PaymentStatus::PENDING, $payment->refresh()->status);
    }

    public function test_a_replayed_callback_is_not_processed_twice(): void
    {
        $traveler = $this->traveler();
        $booking = $this->pendingBookingFor($traveler);
        $this->actingAs($traveler)->post(route('payments.checkout', $booking));
        $payment = $booking->payments()->firstOrFail();

        $this->actingAs($traveler)->post(route('payments.callback', $payment), ['decision' => 'approve']);
        $paidAt = $payment->refresh()->paid_at;

        $this->actingAs($traveler)
            ->post(route('payments.callback', $payment), ['decision' => 'decline'])
            ->assertRedirect(route('traveler.bookings.show', $booking));

        $payment->refresh();
        $this->assertEquals(PaymentStatus::PAID, $payment->status);
        $this->assertEquals($paidAt->toDateTimeString(), $payment->paid_at->toDateTimeString());
    }

    public function test_checkout_on_an_already_paid_booking_redirects_back(): void
    {
        $traveler = $this->traveler();
        $booking = Booking::factory()->paid()->create(['user_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->post(route('payments.checkout', $booking))
            ->assertRedirect(route('traveler.bookings.show', $booking));

        $this->assertEquals(0, $booking->payments()->count());
    }

    public function test_paying_a_resort_booking_confirms_it(): void
    {
        $traveler = $this->traveler();
        $booking = Booking::factory()->resort()->create([
            'user_id' => $traveler->id,
            'booking_status' => BookingStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);

        $this->actingAs($traveler)->post(route('payments.checkout', $booking));
        $payment = $booking->payments()->firstOrFail();
        $this->actingAs($traveler)->post(route('payments.callback', $payment), ['decision' => 'approve']);

        $booking->refresh();
        $this->assertEquals(BookingStatus::CONFIRMED->value, $booking->booking_status);
        $this->assertEquals(PaymentStatus::PAID->value, $booking->payment_status);
        $this->assertEquals(PaymentStatus::PAID, $payment->refresh()->status);
    }

    public function test_a_resort_booking_that_lost_its_room_at_checkout_is_refunded(): void
    {
        $traveler = $this->traveler();
        $rival = $this->traveler();

        $booking = Booking::factory()->resort()->create([
            'user_id' => $traveler->id,
            'booking_status' => BookingStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
        ]);

        // The room only has capacity for one; a rival confirms the same dates
        // while our traveler is still at the checkout page.
        Booking::factory()->create([
            'user_id' => $rival->id,
            'resort_id' => $booking->resort_id,
            'room_id' => $booking->room_id,
            'tour_package_id' => null,
            'booking_type' => $booking->booking_type,
            'travel_date' => null,
            'check_in_date' => $booking->check_in_date->toDateString(),
            'check_out_date' => $booking->check_out_date->toDateString(),
            'booking_status' => BookingStatus::CONFIRMED->value,
            'payment_status' => PaymentStatus::PAID->value,
        ]);

        $this->actingAs($traveler)->post(route('payments.checkout', $booking));
        $payment = $booking->payments()->firstOrFail();

        $this->actingAs($traveler)
            ->post(route('payments.callback', $payment), ['decision' => 'approve'])
            ->assertSessionHasErrors('payment');

        $booking->refresh();
        $payment->refresh();

        $this->assertEquals(BookingStatus::CANCELLED->value, $booking->booking_status);
        $this->assertEquals(PaymentStatus::REFUNDED->value, $booking->payment_status);
        $this->assertEquals(PaymentStatus::REFUNDED, $payment->status);
    }
}
