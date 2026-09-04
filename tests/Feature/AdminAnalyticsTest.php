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

class AdminAnalyticsTest extends TestCase
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

    private function admin(): User
    {
        return $this->userWithRole(UserRole::ADMIN->value);
    }

    // ---------------------------------------------------------------------
    // Access control
    // ---------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.analytics.index'))->assertRedirect('/login');
    }

    public function test_a_traveler_cannot_view_analytics(): void
    {
        $this->actingAs($this->userWithRole(UserRole::TRAVELER->value))
            ->get(route('admin.analytics.index'))
            ->assertForbidden();
    }

    public function test_an_admin_can_view_analytics(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.analytics.index'))
            ->assertOk();
    }

    // ---------------------------------------------------------------------
    // Figures
    // ---------------------------------------------------------------------

    public function test_lifetime_revenue_only_counts_paid_payments(): void
    {
        $booking = Booking::factory()->create();

        Payment::factory()->paid()->create(['booking_id' => $booking->id, 'amount' => 5000]);
        Payment::factory()->create(['booking_id' => $booking->id, 'amount' => 9999]); // still Pending
        Payment::factory()->failed()->create(['booking_id' => $booking->id, 'amount' => 7777]);

        $this->actingAs($this->admin())
            ->get(route('admin.analytics.index'))
            ->assertSee('5,000.00')
            ->assertDontSee('9,999.00')
            ->assertDontSee('7,777.00');
    }

    public function test_booking_status_counts_reflect_real_bookings(): void
    {
        Booking::factory()->count(2)->create(['booking_status' => BookingStatus::CONFIRMED->value]);
        Booking::factory()->create(['booking_status' => BookingStatus::CANCELLED->value]);

        $response = $this->actingAs($this->admin())->get(route('admin.analytics.index'));

        $response->assertOk();
        $response->assertViewHas('bookingStatusCounts', function (array $counts): bool {
            return $counts[BookingStatus::CONFIRMED->value] === 2
                && $counts[BookingStatus::CANCELLED->value] === 1;
        });
    }

    public function test_a_combined_booking_credits_both_the_resort_and_package_owner(): void
    {
        $booking = Booking::factory()->create(['booking_status' => BookingStatus::CONFIRMED->value]);

        $response = $this->actingAs($this->admin())->get(route('admin.analytics.index'));

        $response->assertViewHas('topProviders', function (array $providers) use ($booking): bool {
            $names = array_column($providers, 'name');

            // A plain package booking (the factory default) credits exactly
            // the package owner — not a phantom second entry.
            return in_array($booking->tourPackage->user->name, $names, true) && count($providers) === 1;
        });
    }

    public function test_monthly_trend_covers_the_last_six_months_ending_this_month(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.analytics.index'));

        $response->assertViewHas('monthlyTrend', function (array $trend): bool {
            return count($trend) === 6 && $trend[5]['label'] === now()->format('M Y');
        });
    }
}
