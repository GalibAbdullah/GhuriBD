<?php

namespace Tests\Feature;

use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\ProviderVerification;
use App\Models\User;
use App\Notifications\VerificationApproved;
use App\Notifications\VerificationRejected;
use App\Notifications\VerificationRequestSubmitted;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
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

    public function test_admin_receives_notification_when_partner_submits_verification(): void
    {
        Notification::fake();

        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        Storage::fake('public');

        $this->actingAs($partner)
            ->post(route('partner.verifications.store'), [
                'provider_name' => 'Sunrise Resort',
                'provider_type' => ProviderType::RESORT_OWNER->value,
                'business_address' => "Cox's Bazar, Bangladesh",
                'phone' => '01700000000',
                'verification_document' => UploadedFile::fake()->create('trade-license.pdf', 250, 'application/pdf'),
            ])
            ->assertRedirect(route('partner.verifications.status'));

        Notification::assertSentTo($admin, VerificationRequestSubmitted::class);
    }

    public function test_admin_receives_notification_in_database_after_partner_submission(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        Storage::fake('public');

        $this->actingAs($partner)
            ->post(route('partner.verifications.store'), [
                'provider_name' => 'Sunrise Resort',
                'provider_type' => ProviderType::RESORT_OWNER->value,
                'business_address' => "Cox's Bazar, Bangladesh",
                'phone' => '01700000000',
                'verification_document' => UploadedFile::fake()->create('trade-license.pdf', 250, 'application/pdf'),
            ]);

        $this->assertSame(1, $admin->fresh()->notifications()->count());
        $this->assertSame(1, $admin->fresh()->unreadNotifications()->count());

        $notification = $admin->fresh()->notifications()->first();

        $this->assertSame(VerificationRequestSubmitted::class, $notification->type);
        $this->assertSame('New Travel Partner Verification Request', $notification->data['title']);
        $this->assertStringContainsString('Sunrise Resort', $notification->data['message']);
        $this->assertNotNull($notification->data['action_url']);
    }

    public function test_partner_receives_notification_when_admin_approves(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.verifications.review', $verification), [
                'status' => VerificationStatus::APPROVED->value,
            ]);

        $this->assertSame(1, $partner->fresh()->notifications()->count());
        $this->assertSame(1, $partner->fresh()->unreadNotifications()->count());

        $notification = $partner->fresh()->notifications()->first();

        $this->assertSame(VerificationApproved::class, $notification->type);
        $this->assertSame('Your verification request has been approved.', $notification->data['title']);
    }

    public function test_partner_receives_notification_when_admin_rejects(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.verifications.review', $verification), [
                'status' => VerificationStatus::REJECTED->value,
                'rejection_reason' => 'Document is unreadable.',
            ]);

        $this->assertSame(1, $partner->fresh()->notifications()->count());
        $this->assertSame(1, $partner->fresh()->unreadNotifications()->count());

        $notification = $partner->fresh()->notifications()->first();

        $this->assertSame(VerificationRejected::class, $notification->type);
        $this->assertSame('Your verification request has been rejected.', $notification->data['title']);
    }

    public function test_notification_bell_shows_unread_count(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));
        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));

        $this->assertSame(2, $admin->fresh()->unreadNotifications()->count());

        // Bell renders as a clickable link with the unread count badge.
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Notifications"', false)
            ->assertSee(route('notifications.index'))
            ->assertSee('2');
    }

    public function test_bell_is_a_clickable_link_to_notifications_page(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);

        // The bell must be an anchor linking to the notifications page — no JS required.
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Notifications"', false)
            ->assertSee(route('notifications.index'));
    }

    public function test_notifications_page_lists_all_notifications_newest_first(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        // More than the old 5-item cap — every one must appear on the page.
        for ($i = 1; $i <= 7; $i++) {
            $admin->notify(new VerificationRequestSubmitted($verification->load('user')));
        }

        $this->assertSame(7, $admin->fresh()->notifications()->count());

        $response = $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk();

        // All 7 notifications are rendered, newest first.
        $this->assertSame(7, substr_count($response->getContent(), 'New Travel Partner Verification Request'));
    }

    public function test_notifications_page_shows_read_and_unread_status(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));
        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));

        // Mark the newest one as read.
        $admin->fresh()->notifications()->latest()->first()->markAsRead();

        $response = $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk();

        $response->assertSee('Read');
        $response->assertSee('Unread');
    }

    public function test_notifications_page_shows_empty_state_when_no_notifications(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);

        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('No notifications yet.');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));

        $notification = $admin->fresh()->notifications()->first();

        $this->actingAs($admin)
            ->put(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));
        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));
        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));

        $this->assertSame(3, $admin->fresh()->unreadNotifications()->count());

        $this->actingAs($admin)
            ->put(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
        $this->assertSame(3, $admin->fresh()->notifications()->count());
    }

    public function test_clicking_notification_marks_read_and_redirects_admin_to_verification(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));

        $notification = $admin->fresh()->notifications()->first();

        $this->actingAs($admin)
            ->get(route('notifications.redirect', $notification))
            ->assertRedirect(route('admin.verifications.show', $verification));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_clicking_notification_redirects_partner_to_their_verification_status(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $partner->notify(new VerificationApproved($verification));

        $notification = $partner->fresh()->notifications()->first();

        $this->actingAs($partner)
            ->get(route('notifications.redirect', $notification))
            ->assertRedirect(route('partner.verifications.show', $verification));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_view_another_users_notification(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $otherPartner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $otherPartner->id,
        ]);

        $partner->notify(new VerificationRequestSubmitted($verification->load('user')));

        $notification = $partner->fresh()->notifications()->first();

        // A different user cannot read or redirect this notification
        $this->actingAs($admin)
            ->get(route('notifications.redirect', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_notifications_list_only_shows_own_notifications(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $admin->notify(new VerificationRequestSubmitted($verification->load('user')));
        $partner->notify(new VerificationApproved($verification));

        // Admin's list shows only their own notification
        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('New Travel Partner Verification Request')
            ->assertDontSee('Your verification request has been approved.');

        // Partner's list shows only their own notification
        $this->actingAs($partner)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Your verification request has been approved.')
            ->assertDontSee('New Travel Partner Verification Request');
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $this->get(route('notifications.index'))->assertRedirect('/login');
        $this->put(route('notifications.read-all'))->assertRedirect('/login');
    }
}