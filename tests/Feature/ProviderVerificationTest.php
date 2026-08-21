<?php

namespace Tests\Feature;

use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\ProviderVerification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProviderVerificationTest extends TestCase
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

    public function test_new_verification_starts_as_pending(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->create([
            'user_id' => $partner->id,
        ]);

        $this->assertSame(VerificationStatus::PENDING->value, $verification->status);
        $this->assertTrue($verification->isPending());
    }

    public function test_travel_partner_can_view_verification_status_page(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->create([
            'user_id' => $partner->id,
            'status' => VerificationStatus::PENDING->value,
        ]);

        $this->actingAs($partner)
            ->get(route('partner.verifications.status'))
            ->assertOk()
            ->assertSee('Verification Status')
            ->assertSee('Pending');
    }

    public function test_travel_partner_can_view_create_form(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->get(route('partner.verifications.create'))
            ->assertOk()
            ->assertSee('Provider Verification')
            ->assertSee('Resort Owner')
            ->assertSee('Tour Operator')
            ->assertSee('Tour Guide');
    }

    public function test_travel_partner_can_submit_verification(): void
    {
        Storage::fake('public');

        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->post(route('partner.verifications.store'), [
                'provider_name' => 'Sunrise Resort',
                'provider_type' => ProviderType::RESORT_OWNER->value,
                'business_address' => 'Cox\'s Bazar, Bangladesh',
                'phone' => '01700000000',
                'verification_document' => UploadedFile::fake()->create('trade-license.pdf', 250, 'application/pdf'),
                'additional_information' => 'Family business since 2010.',
            ])
            ->assertRedirect(route('partner.verifications.status'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('provider_verifications', [
            'user_id' => $partner->id,
            'provider_name' => 'Sunrise Resort',
            'provider_type' => ProviderType::RESORT_OWNER->value,
            'business_address' => 'Cox\'s Bazar, Bangladesh',
            'phone' => '01700000000',
            'additional_information' => 'Family business since 2010.',
            'status' => VerificationStatus::PENDING->value,
        ]);

        $verification = $partner->providerVerifications()->firstOrFail();

        $this->assertNotNull($verification->verification_document);
        Storage::disk('public')->assertExists($verification->verification_document);
    }

    public function test_verification_submission_requires_all_fields(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->post(route('partner.verifications.store'), [])
            ->assertSessionHasErrors([
                'provider_name',
                'provider_type',
                'business_address',
                'phone',
                'verification_document',
            ]);
    }

    public function test_verification_rejects_invalid_provider_type(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->post(route('partner.verifications.store'), [
                'provider_name' => 'Valid Name',
                'provider_type' => 'Hotel Owner',
                'business_address' => 'Dhaka',
                'phone' => '01700000000',
                'verification_document' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('provider_type');
    }

    public function test_verification_rejects_non_document_file(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->post(route('partner.verifications.store'), [
                'provider_name' => 'Valid Name',
                'provider_type' => ProviderType::TOUR_OPERATOR->value,
                'business_address' => 'Dhaka',
                'phone' => '01700000000',
                'verification_document' => UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('verification_document');
    }

    public function test_verification_rejects_oversized_document(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->post(route('partner.verifications.store'), [
                'provider_name' => 'Valid Name',
                'provider_type' => ProviderType::TOUR_GUIDE->value,
                'business_address' => 'Dhaka',
                'phone' => '01700000000',
                'verification_document' => UploadedFile::fake()->create('big.pdf', 11000, 'application/pdf'),
            ])
            ->assertSessionHasErrors('verification_document');
    }

    public function test_traveler_cannot_access_verification_management(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)->get(route('partner.verifications.status'))->assertForbidden();
        $this->actingAs($traveler)->get(route('partner.verifications.create'))->assertForbidden();
        $this->actingAs($traveler)->post(route('partner.verifications.store'), [])->assertForbidden();
        $this->actingAs($traveler)->get(route('admin.verifications.index'))->assertForbidden();
    }

    public function test_guest_cannot_access_verification_management(): void
    {
        $this->get(route('partner.verifications.status'))->assertRedirect('/login');
        $this->get(route('partner.verifications.create'))->assertRedirect('/login');
        $this->get(route('admin.verifications.index'))->assertRedirect('/login');
    }

    public function test_admin_can_view_all_verification_requests(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->count(3)->create(['user_id' => $partner->id]);

        $this->actingAs($admin)
            ->get(route('admin.verifications.index'))
            ->assertOk()
            ->assertSee('Verification Queue');
    }

    public function test_partner_cannot_access_admin_verification_queue(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->get(route('admin.verifications.index'))
            ->assertForbidden();
    }

    public function test_admin_can_approve_pending_verification(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.verifications.review', $verification), [
                'status' => VerificationStatus::APPROVED->value,
            ])
            ->assertRedirect(route('admin.verifications.index'))
            ->assertSessionHas('status');

        $verification->refresh();

        $this->assertTrue($verification->isApproved());
        $this->assertSame($admin->id, $verification->reviewed_by);
        $this->assertNotNull($verification->reviewed_at);
        $this->assertNull($verification->rejection_reason);

        // Approved partners are considered verified
        $this->assertTrue($partner->isVerifiedProvider());
    }

    public function test_admin_can_reject_pending_verification_with_reason(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.verifications.review', $verification), [
                'status' => VerificationStatus::REJECTED->value,
                'rejection_reason' => 'Document was unreadable.',
            ])
            ->assertRedirect(route('admin.verifications.index'));

        $verification->refresh();

        $this->assertTrue($verification->isRejected());
        $this->assertSame('Document was unreadable.', $verification->rejection_reason);
        $this->assertSame($admin->id, $verification->reviewed_by);

        // Rejected partners are NOT verified
        $this->assertFalse($partner->isVerifiedProvider());
    }

    public function test_rejection_requires_a_reason(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.verifications.review', $verification), [
                'status' => VerificationStatus::REJECTED->value,
            ])
            ->assertSessionHasErrors('rejection_reason');
    }

    public function test_admin_cannot_approve_own_verification(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);

        // An Admin might somehow have a verification row — ensure they can't approve it
        $verification = ProviderVerification::factory()->pending()->create([
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.verifications.review', $verification), [
                'status' => VerificationStatus::APPROVED->value,
            ])
            ->assertForbidden();

        $this->assertTrue($verification->fresh()->isPending());
    }

    public function test_travel_partner_can_view_own_verification_details(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $verification = ProviderVerification::factory()->create([
            'user_id' => $partner->id,
        ]);

        $this->actingAs($partner)
            ->get(route('partner.verifications.show', $verification))
            ->assertOk()
            ->assertSee($verification->provider_name);
    }

    public function test_travel_partner_cannot_view_another_partners_verification(): void
    {
        $otherPartner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $verification = ProviderVerification::factory()->create([
            'user_id' => $otherPartner->id,
        ]);

        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)
            ->get(route('partner.verifications.show', $verification))
            ->assertForbidden();
    }

    public function test_partner_sees_rejected_status_with_reason(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->rejected()->create([
            'user_id' => $partner->id,
        ]);

        $this->actingAs($partner)
            ->get(route('partner.verifications.status'))
            ->assertOk()
            ->assertSee('Rejected')
            ->assertSee('The submitted document could not be verified');
    }

    public function test_partner_is_verified_after_approval(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->assertFalse($partner->isVerifiedProvider());

        ProviderVerification::factory()->approved()->create([
            'user_id' => $partner->id,
        ]);

        $this->assertTrue($partner->fresh()->isVerifiedProvider());
    }

    public function test_verification_document_url_is_accessible_from_storage(): void
    {
        Storage::fake('public');

        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $verification = ProviderVerification::factory()->create([
            'user_id' => $partner->id,
            'verification_document' => 'verification-documents/test-file.pdf',
        ]);

        Storage::disk('public')->put('verification-documents/test-file.pdf', 'pdf content');

        $this->actingAs($partner)
            ->get(route('partner.verifications.show', $verification))
            ->assertOk()
            ->assertSee('View verification document');
    }

    public function test_admin_dashboard_shows_pending_verification_count(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->count(2)->create([
            'user_id' => $partner->id,
            'status' => VerificationStatus::PENDING->value,
        ]);

        ProviderVerification::factory()->approved()->create([
            'user_id' => $partner->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pending verifications')
            ->assertSee('2');
    }

    public function test_admin_dashboard_lists_pending_verifications_with_review_link(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $pending = ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
            'provider_name' => 'Sunrise Resort',
        ]);

        $approved = ProviderVerification::factory()->approved()->create([
            'user_id' => $partner->id,
            'provider_name' => 'Approved Tours',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Sunrise Resort')
            ->assertDontSee('Approved Tours')
            ->assertSee(route('admin.verifications.show', $pending));
    }

    public function test_admin_dashboard_shows_empty_state_when_no_pending_verifications(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('No pending verifications');
    }

    public function test_admin_dashboard_counts_only_pending_verifications(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->pending()->create([
            'user_id' => $partner->id,
            'provider_name' => 'Pending One',
        ]);

        ProviderVerification::factory()->approved()->create([
            'user_id' => $partner->id,
            'provider_name' => 'Approved One',
        ]);

        ProviderVerification::factory()->rejected()->create([
            'user_id' => $partner->id,
            'provider_name' => 'Rejected One',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pending One')
            ->assertDontSee('Approved One')
            ->assertDontSee('Rejected One')
            ->assertSee('Pending verifications')
            ->assertSee('1');
    }
}
