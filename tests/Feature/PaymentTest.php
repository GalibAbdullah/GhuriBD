<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->actingAs($traveler)->get(route('payments.checkout', $booking));

        $payment = $booking->payments()->firstOrFail();

        $response->assertRedirect(route('payments.show', $payment));
        $this->assertEquals(PaymentStatus::PENDING->value, $payment->status->value);
        $this->assertEquals((string) $booking->total_amount, (string) $payment->amount);
    }

    public function test_approving_payment_confirms_the_booking(): void
    {
        $traveler = $this->traveler();
        $booking = $this->pendingBookingFor($traveler);
        $this->actingAs($traveler)->get(route('payments.checkout', $booking));
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
    }

    public function test_declining_payment_leaves_the_booking_pending(): void
    {
        $traveler = $this->traveler();
        $booking = $this->pendingBookingFor($traveler);
        $this->actingAs($traveler)->get(route('payments.checkout', $booking));
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
        $this->actingAs($owner)->get(route('payments.checkout', $booking));
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
        $this->actingAs($traveler)->get(route('payments.checkout', $booking));
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
            ->get(route('payments.checkout', $booking))
            ->assertRedirect(route('traveler.bookings.show', $booking));

        $this->assertEquals(0, $booking->payments()->count());
    }
}
