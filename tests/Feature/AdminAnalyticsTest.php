<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\Payment;
use App\Models\ProviderVerification;
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

    private function verifiedGuide(): User
    {
        $guide = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->approved()->create([
            'user_id' => $guide->id,
            'provider_type' => ProviderType::TOUR_GUIDE->value,
        ]);

        return $guide;
    }

    // ---------------------------------------------------------------------
    // Access control
    // ---------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.analytics.index'))->assertRedirect('/login');
    }

    public function test_non_admin_cannot_view_analytics(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)
            ->get(route('admin.analytics.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_analytics(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.analytics.index'))
            ->assertOk();
    }

    // ---------------------------------------------------------------------
    // Booking status breakdown
    // ---------------------------------------------------------------------

    public function test_booking_status_counts_are_accurate(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id]);

        Booking::factory()->forSlot($slot)->count(2)->create(['traveler_id' => $traveler->id]);
        Booking::factory()->forSlot($slot)->confirmed()->create(['traveler_id' => $traveler->id]);
        Booking::factory()->forSlot($slot)->cancelled()->create(['traveler_id' => $traveler->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertViewHas('bookingStatusCounts', [
                BookingStatus::PENDING_PAYMENT->value => 2,
                BookingStatus::CONFIRMED->value => 1,
                BookingStatus::CANCELLED->value => 1,
                BookingStatus::COMPLETED->value => 0,
            ]);
    }

    // ---------------------------------------------------------------------
    // Revenue
    // ---------------------------------------------------------------------

    public function test_lifetime_revenue_only_counts_succeeded_payments(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id]);
        $booking = Booking::factory()->forSlot($slot)->confirmed()->create(['traveler_id' => $traveler->id]);

        Payment::factory()->succeeded()->create(['booking_id' => $booking->id, 'amount' => 5000]);
        Payment::factory()->failed()->create(['booking_id' => $booking->id, 'amount' => 9999]);

        $response = $this->actingAs($this->admin())->get(route('admin.analytics.index'))->assertOk();

        $response->assertSee(number_format(5000, 2), false);
        $response->assertDontSee(number_format(9999, 2), false);
    }

    public function test_admin_dashboard_this_month_stats_exclude_last_month(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $slot = GuideAvailability::factory()->create(['user_id' => $guide->id]);

        $thisMonthBooking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);

        $lastMonthBooking = Booking::factory()->forSlot($slot)->create(['traveler_id' => $traveler->id]);
        $lastMonthBooking->forceFill(['created_at' => now()->subMonthNoOverflow()])->save();

        Payment::factory()->succeeded()->create([
            'booking_id' => $thisMonthBooking->id,
            'amount' => 1234,
            'paid_at' => now(),
        ]);
        Payment::factory()->succeeded()->create([
            'booking_id' => $lastMonthBooking->id,
            'amount' => 9876,
            'paid_at' => now()->subMonthNoOverflow(),
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk();

        $response->assertSee(number_format(1234, 2), false);
        $response->assertDontSee(number_format(9876, 2), false);
    }

    // ---------------------------------------------------------------------
    // Top guides
    // ---------------------------------------------------------------------

    public function test_top_guides_are_ranked_by_confirmed_revenue_only(): void
    {
        $bigEarner = $this->verifiedGuide();
        $smallEarner = $this->verifiedGuide();
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $bigSlot = GuideAvailability::factory()->create(['user_id' => $bigEarner->id]);
        $smallSlot = GuideAvailability::factory()->create(['user_id' => $smallEarner->id]);

        Booking::factory()->forSlot($bigSlot, 1)->confirmed()->create([
            'traveler_id' => $traveler->id, 'unit_price' => 20000, 'total_price' => 20000,
        ]);
        Booking::factory()->forSlot($smallSlot, 1)->confirmed()->create([
            'traveler_id' => $traveler->id, 'unit_price' => 500, 'total_price' => 500,
        ]);
        // A pending (unpaid) booking against the small earner must not count.
        Booking::factory()->forSlot($smallSlot, 1)->create([
            'traveler_id' => $traveler->id, 'unit_price' => 999999, 'total_price' => 999999,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.analytics.index'))->assertOk();
        $content = $response->getContent();

        $this->assertLessThan(
            strpos($content, $smallEarner->name),
            strpos($content, $bigEarner->name),
            'Expected the higher-revenue guide to be listed before the lower-revenue one.'
        );
        $this->assertStringNotContainsString(number_format(999999, 2), $content);
    }

    // ---------------------------------------------------------------------
    // Users & verification funnel
    // ---------------------------------------------------------------------

    public function test_user_counts_by_role_are_accurate(): void
    {
        $this->userWithRole(UserRole::TRAVELER->value);
        $this->userWithRole(UserRole::TRAVELER->value);
        $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($this->admin())
            ->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertViewHas('usersByRole', [
                UserRole::TRAVELER->value => 2,
                UserRole::TRAVEL_PARTNER->value => 1,
                UserRole::ADMIN->value => 1,
            ]);
    }

    public function test_verification_funnel_counts_are_accurate(): void
    {
        $pending = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        ProviderVerification::factory()->pending()->create(['user_id' => $pending->id]);

        $rejected = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        ProviderVerification::factory()->rejected()->create(['user_id' => $rejected->id]);

        $this->verifiedGuide();

        $this->actingAs($this->admin())
            ->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertViewHas('verificationCounts', [
                VerificationStatus::PENDING->value => 1,
                VerificationStatus::APPROVED->value => 1,
                VerificationStatus::REJECTED->value => 1,
            ]);
    }
}
