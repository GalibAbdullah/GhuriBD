<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Notifications\NewReviewReceived;
use App\Notifications\ReviewSubmitted;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewManagementTest extends TestCase
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

    private function completedResortBooking(User $traveler): Booking
    {
        return Booking::factory()->resort()->completed()->create(['user_id' => $traveler->id]);
    }

    // --- Access control -----------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $booking = $this->completedResortBooking($traveler);

        $this->get(route('traveler.reviews.create', $booking))->assertRedirect('/login');
    }

    public function test_traveler_can_review_a_completed_booking(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $booking = $this->completedResortBooking($traveler);

        Notification::fake();

        $response = $this->actingAs($traveler)->post(route('traveler.reviews.store', $booking), [
            'rating' => 5,
            'review_text' => 'Wonderful stay, would come back again.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'user_id' => $traveler->id,
            'resort_id' => $booking->resort_id,
            'rating' => 5,
        ]);

        Notification::assertSentTo($traveler, ReviewSubmitted::class);
        Notification::assertSentTo($booking->resort->user, NewReviewReceived::class);
    }

    public function test_traveler_cannot_review_a_pending_or_confirmed_booking(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $booking = Booking::factory()->resort()->create(['user_id' => $traveler->id]);

        $this->actingAs($traveler)
            ->post(route('traveler.reviews.store', $booking), ['rating' => 5, 'review_text' => 'Great!'])
            ->assertForbidden();

        $this->assertDatabaseMissing('reviews', ['booking_id' => $booking->id]);
    }

    public function test_traveler_cannot_review_someone_elses_booking(): void
    {
        $owner = $this->userWithRole(UserRole::TRAVELER->value);
        $intruder = $this->userWithRole(UserRole::TRAVELER->value);
        $booking = $this->completedResortBooking($owner);

        $this->actingAs($intruder)
            ->post(route('traveler.reviews.store', $booking), ['rating' => 5, 'review_text' => 'Great!'])
            ->assertForbidden();
    }

    public function test_duplicate_review_is_prevented(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $booking = $this->completedResortBooking($traveler);

        $this->actingAs($traveler)->post(route('traveler.reviews.store', $booking), [
            'rating' => 4,
            'review_text' => 'Good stay.',
        ]);

        $this->actingAs($traveler)
            ->post(route('traveler.reviews.store', $booking), ['rating' => 2, 'review_text' => 'Actually, meh.'])
            ->assertForbidden();

        $this->assertSame(1, Review::where('booking_id', $booking->id)->count());
    }

    // --- Validation -------------------------------------------------------

    public function test_rating_must_be_an_integer_between_1_and_5(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $booking = $this->completedResortBooking($traveler);

        $this->actingAs($traveler)
            ->post(route('traveler.reviews.store', $booking), ['rating' => 6, 'review_text' => 'Great!'])
            ->assertSessionHasErrors('rating');

        $this->actingAs($traveler)
            ->post(route('traveler.reviews.store', $booking), ['rating' => 0, 'review_text' => 'Great!'])
            ->assertSessionHasErrors('rating');

        $this->actingAs($traveler)
            ->post(route('traveler.reviews.store', $booking), ['review_text' => 'Great!'])
            ->assertSessionHasErrors('rating');
    }

    // --- Behavior -------------------------------------------------------

    public function test_average_rating_updates_correctly(): void
    {
        $traveler1 = $this->userWithRole(UserRole::TRAVELER->value);
        $traveler2 = $this->userWithRole(UserRole::TRAVELER->value);

        $booking1 = $this->completedResortBooking($traveler1);
        $resort = $booking1->resort;

        $booking2 = Booking::factory()->completed()->create([
            'user_id' => $traveler2->id,
            'resort_id' => $resort->id,
            'tour_package_id' => null,
            'room_id' => $booking1->room_id,
        ]);

        Review::factory()->create([
            'user_id' => $traveler1->id,
            'booking_id' => $booking1->id,
            'resort_id' => $resort->id,
            'tour_package_id' => null,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $traveler2->id,
            'booking_id' => $booking2->id,
            'resort_id' => $resort->id,
            'tour_package_id' => null,
            'rating' => 2,
        ]);

        $this->assertSame(3.0, $resort->fresh()->averageRating());
    }

    public function test_travel_partner_sees_only_reviews_for_their_own_resorts_and_packages(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $otherPartner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $ownBooking = Booking::factory()->resort()->completed()->create(['user_id' => $traveler->id]);
        $ownBooking->resort->update(['user_id' => $partner->id]);
        $ownReview = Review::factory()->create([
            'user_id' => $traveler->id,
            'booking_id' => $ownBooking->id,
            'resort_id' => $ownBooking->resort_id,
            'tour_package_id' => null,
        ]);

        $otherBooking = Booking::factory()->resort()->completed()->create(['user_id' => $traveler->id]);
        $otherBooking->resort->update(['user_id' => $otherPartner->id]);
        $otherReview = Review::factory()->create([
            'user_id' => $traveler->id,
            'booking_id' => $otherBooking->id,
            'resort_id' => $otherBooking->resort_id,
            'tour_package_id' => null,
        ]);

        $response = $this->actingAs($partner)->get(route('partner.reviews.index'));

        $response->assertOk();
        $response->assertSee($ownReview->review_text);
        $response->assertDontSee($otherReview->review_text);
    }

    public function test_travel_partner_can_reply_to_a_review_on_their_own_resort(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $booking = Booking::factory()->resort()->completed()->create(['user_id' => $traveler->id]);
        $booking->resort->update(['user_id' => $partner->id]);
        $review = Review::factory()->create([
            'user_id' => $traveler->id,
            'booking_id' => $booking->id,
            'resort_id' => $booking->resort_id,
            'tour_package_id' => null,
            'rating' => 5,
        ]);

        $this->actingAs($partner)
            ->patch(route('partner.reviews.reply', $review), ['partner_reply' => 'Thank you for staying with us!'])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'partner_reply' => 'Thank you for staying with us!',
            'rating' => 5,
        ]);
    }

    public function test_travel_partner_cannot_reply_to_another_partners_review(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $owningPartner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $booking = Booking::factory()->resort()->completed()->create(['user_id' => $traveler->id]);
        $booking->resort->update(['user_id' => $owningPartner->id]);
        $review = Review::factory()->create([
            'user_id' => $traveler->id,
            'booking_id' => $booking->id,
            'resort_id' => $booking->resort_id,
            'tour_package_id' => null,
        ]);

        $this->actingAs($partner)
            ->patch(route('partner.reviews.reply', $review), ['partner_reply' => 'Not mine to answer.'])
            ->assertForbidden();
    }

    public function test_admin_can_delete_an_inappropriate_review(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $booking = $this->completedResortBooking($traveler);
        $review = Review::factory()->create([
            'user_id' => $traveler->id,
            'booking_id' => $booking->id,
            'resort_id' => $booking->resort_id,
            'tour_package_id' => null,
        ]);

        $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review))->assertRedirect();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_traveler_cannot_delete_a_review(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $booking = $this->completedResortBooking($traveler);
        $review = Review::factory()->create([
            'user_id' => $traveler->id,
            'booking_id' => $booking->id,
            'resort_id' => $booking->resort_id,
            'tour_package_id' => null,
        ]);

        $this->actingAs($traveler)->delete(route('admin.reviews.destroy', $review))->assertForbidden();
    }
}
