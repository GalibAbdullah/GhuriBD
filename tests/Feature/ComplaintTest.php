<?php

namespace Tests\Feature;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintResponded;
use App\Notifications\ComplaintSubmitted;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ComplaintTest extends TestCase
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

    public function test_a_traveler_can_file_a_complaint_and_admins_are_notified(): void
    {
        Notification::fake();

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $admin = $this->userWithRole(UserRole::ADMIN->value);

        $response = $this->actingAs($traveler)->post(route('complaints.store'), [
            'category' => ComplaintCategory::PAYMENT->value,
            'subject' => 'Charged twice for one booking',
            'description' => 'I was charged twice for the same booking reference.',
        ]);

        $complaint = Complaint::sole();
        $response->assertRedirect(route('complaints.show', $complaint));

        $this->assertEquals($traveler->id, $complaint->user_id);
        $this->assertEquals(ComplaintStatus::OPEN->value, $complaint->status);

        Notification::assertSentTo($admin, ComplaintSubmitted::class);
    }

    public function test_a_traveler_cannot_view_another_users_complaint(): void
    {
        $owner = $this->userWithRole(UserRole::TRAVELER->value);
        $stranger = $this->userWithRole(UserRole::TRAVELER->value);
        $complaint = Complaint::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($stranger)
            ->get(route('complaints.show', $complaint))
            ->assertForbidden();
    }

    public function test_an_admin_can_view_any_complaint(): void
    {
        $owner = $this->userWithRole(UserRole::TRAVELER->value);
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $complaint = Complaint::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($admin)
            ->get(route('complaints.show', $complaint))
            ->assertOk();
    }

    public function test_an_admin_can_respond_and_the_complainant_is_notified(): void
    {
        Notification::fake();

        $owner = $this->userWithRole(UserRole::TRAVELER->value);
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $complaint = Complaint::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($admin)
            ->put(route('admin.complaints.respond', $complaint), [
                'status' => ComplaintStatus::RESOLVED->value,
                'admin_response' => 'We have refunded the duplicate charge.',
            ])
            ->assertRedirect(route('complaints.show', $complaint));

        $complaint->refresh();
        $this->assertEquals(ComplaintStatus::RESOLVED->value, $complaint->status);
        $this->assertEquals($admin->id, $complaint->resolved_by);
        $this->assertNotNull($complaint->resolved_at);

        Notification::assertSentTo($owner, ComplaintResponded::class);
    }

    public function test_a_non_admin_cannot_respond_to_a_complaint(): void
    {
        $owner = $this->userWithRole(UserRole::TRAVELER->value);
        $complaint = Complaint::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->put(route('admin.complaints.respond', $complaint), [
                'status' => ComplaintStatus::RESOLVED->value,
                'admin_response' => 'Trying to resolve my own complaint.',
            ])
            ->assertForbidden();
    }

    public function test_index_shows_only_the_users_own_complaints_but_admins_see_all(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $otherTraveler = $this->userWithRole(UserRole::TRAVELER->value);
        $admin = $this->userWithRole(UserRole::ADMIN->value);

        Complaint::factory()->create(['user_id' => $traveler->id]);
        Complaint::factory()->create(['user_id' => $otherTraveler->id]);

        $this->actingAs($traveler)
            ->get(route('complaints.index'))
            ->assertSee($traveler->complaints()->first()->subject)
            ->assertDontSee($otherTraveler->complaints()->first()->subject);

        $this->actingAs($admin)
            ->get(route('complaints.index'))
            ->assertSee($traveler->complaints()->first()->subject)
            ->assertSee($otherTraveler->complaints()->first()->subject);
    }
}
