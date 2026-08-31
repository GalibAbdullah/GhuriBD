<?php

namespace Tests\Feature;

use App\Enums\TourPackageService;
use App\Enums\TourPackageStatus;
use App\Enums\UserRole;
use App\Models\ProviderVerification;
use App\Models\TourPackage;
use App\Models\TourPackageImage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TourPackageManagementTest extends TestCase
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

    private function verifiedPartner(): User
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->approved()->create(['user_id' => $partner->id]);

        return $partner;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Sundarbans Adventure',
            'destination' => 'Sundarbans',
            'division' => 'Khulna',
            'district' => 'Khulna',
            'description' => 'A guided tour through the mangrove forest.',
            'duration_days' => 3,
            'duration_nights' => 2,
            'price' => 12000,
            'max_travelers' => 15,
            'meeting_point' => 'Khulna Launch Terminal',
            'start_location' => 'Khulna',
            'itinerary' => "Day 1: Arrival.\nDay 2: Boat safari.\nDay 3: Departure.",
            'included_services' => [TourPackageService::TRANSPORT->value, TourPackageService::MEALS->value],
            'excluded_services' => [TourPackageService::PHOTOGRAPHY->value],
            'status' => TourPackageStatus::ACTIVE->value,
        ], $overrides);
    }

    // --- Access control -----------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('partner.packages.index'))->assertRedirect('/login');
    }

    public function test_traveler_cannot_access_partner_package_management(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)->get(route('partner.packages.create'))->assertForbidden();
        $this->actingAs($traveler)->post(route('partner.packages.store'), [])->assertForbidden();
    }

    public function test_unverified_travel_partner_is_blocked(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)->get(route('partner.packages.create'))->assertForbidden();
        $this->actingAs($partner)->post(route('partner.packages.store'), $this->validPayload())->assertForbidden();
    }

    public function test_admin_is_blocked_from_partner_package_write_routes(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        $package = TourPackage::factory()->create(['user_id' => $partner->id]);

        $this->actingAs($admin)->get(route('partner.packages.create'))->assertForbidden();
        $this->actingAs($admin)->put(route('partner.packages.update', $package), [])->assertForbidden();
        $this->actingAs($admin)->delete(route('partner.packages.destroy', $package))->assertForbidden();
    }

    // --- CRUD for a verified partner -----------------------------------

    public function test_verified_partner_sees_empty_state_with_no_packages(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->get(route('partner.packages.index'))
            ->assertOk()
            ->assertSee('Tour Package', false);
    }

    public function test_verified_partner_can_create_package_with_cover_and_gallery_images(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();

        $response = $this->actingAs($partner)->post(route('partner.packages.store'), $this->validPayload([
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            'gallery_images' => [
                UploadedFile::fake()->create('gallery-1.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('gallery-2.jpg', 100, 'image/jpeg'),
            ],
        ]));

        $package = TourPackage::where('title', 'Sundarbans Adventure')->firstOrFail();

        $response->assertRedirect(route('partner.packages.show', $package))->assertSessionHas('status');

        $this->assertDatabaseHas('tour_packages', [
            'user_id' => $partner->id,
            'title' => 'Sundarbans Adventure',
            'division' => 'Khulna',
            'district' => 'Khulna',
            'status' => TourPackageStatus::ACTIVE->value,
        ]);

        Storage::disk('public')->assertExists($package->cover_image);
        $this->assertCount(2, $package->images);
        $package->images->each(fn (TourPackageImage $image) => Storage::disk('public')->assertExists($image->image_path));
    }

    public function test_package_creation_requires_the_core_fields(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->post(route('partner.packages.store'), [])
            ->assertSessionHasErrors([
                'title', 'destination', 'division', 'district', 'description',
                'duration_days', 'duration_nights', 'price', 'max_travelers',
                'meeting_point', 'start_location', 'itinerary', 'status', 'cover_image',
            ]);
    }

    public function test_package_creation_validates_district_belongs_to_division(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->post(route('partner.packages.store'), $this->validPayload([
                'division' => 'Sylhet',
                'district' => 'Khulna', // belongs to Khulna division, not Sylhet
                'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors('district');
    }

    public function test_package_creation_rejects_an_unknown_included_service(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->post(route('partner.packages.store'), $this->validPayload([
                'included_services' => ['Fireworks'],
                'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors('included_services.0');
    }

    public function test_verified_partner_can_view_own_package(): void
    {
        $partner = $this->verifiedPartner();
        $package = TourPackage::factory()->create(['user_id' => $partner->id, 'title' => 'My Own Package']);

        $this->actingAs($partner)
            ->get(route('partner.packages.show', $package))
            ->assertOk()
            ->assertSee('My Own Package');
    }

    public function test_partner_cannot_view_another_partners_package(): void
    {
        $owner = $this->verifiedPartner();
        $package = TourPackage::factory()->create(['user_id' => $owner->id]);

        $otherPartner = $this->verifiedPartner();

        $this->actingAs($otherPartner)
            ->get(route('partner.packages.show', $package))
            ->assertForbidden();
    }

    public function test_partner_cannot_update_another_partners_package(): void
    {
        $owner = $this->verifiedPartner();
        $package = TourPackage::factory()->create(['user_id' => $owner->id]);

        $otherPartner = $this->verifiedPartner();

        $this->actingAs($otherPartner)
            ->put(route('partner.packages.update', $package), $this->validPayload())
            ->assertForbidden();
    }

    public function test_partner_cannot_delete_another_partners_package(): void
    {
        $owner = $this->verifiedPartner();
        $package = TourPackage::factory()->create(['user_id' => $owner->id]);

        $otherPartner = $this->verifiedPartner();

        $this->actingAs($otherPartner)
            ->delete(route('partner.packages.destroy', $package))
            ->assertForbidden();

        $this->assertDatabaseHas('tour_packages', ['id' => $package->id]);
    }

    public function test_verified_partner_can_update_package_and_replace_cover_image(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $package = TourPackage::factory()->create([
            'user_id' => $partner->id,
            'cover_image' => 'tour-packages/cover/old.jpg',
        ]);
        Storage::disk('public')->put('tour-packages/cover/old.jpg', 'old-content');

        $this->actingAs($partner)
            ->put(route('partner.packages.update', $package), $this->validPayload([
                'title' => 'Updated Package Title',
                'cover_image' => UploadedFile::fake()->create('new-cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertRedirect(route('partner.packages.show', $package));

        $package->refresh();

        $this->assertSame('Updated Package Title', $package->title);
        Storage::disk('public')->assertMissing('tour-packages/cover/old.jpg');
        Storage::disk('public')->assertExists($package->cover_image);
    }

    public function test_updating_package_can_remove_a_gallery_image(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $package = TourPackage::factory()->create(['user_id' => $partner->id]);
        $image = TourPackageImage::factory()->create([
            'tour_package_id' => $package->id,
            'image_path' => 'tour-packages/gallery/keep-me.jpg',
        ]);
        Storage::disk('public')->put($image->image_path, 'gallery-content');

        $this->actingAs($partner)
            ->put(route('partner.packages.update', $package), $this->validPayload([
                'remove_gallery_images' => [$image->id],
            ]))
            ->assertRedirect(route('partner.packages.show', $package));

        $this->assertDatabaseMissing('tour_package_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->image_path);
    }

    public function test_deleting_package_removes_cover_and_gallery_files(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $package = TourPackage::factory()->create([
            'user_id' => $partner->id,
            'cover_image' => 'tour-packages/cover/cover.jpg',
        ]);
        Storage::disk('public')->put($package->cover_image, 'cover-content');

        $galleryImage = TourPackageImage::factory()->create([
            'tour_package_id' => $package->id,
            'image_path' => 'tour-packages/gallery/gallery.jpg',
        ]);
        Storage::disk('public')->put($galleryImage->image_path, 'gallery-content');

        $this->actingAs($partner)
            ->delete(route('partner.packages.destroy', $package))
            ->assertRedirect(route('partner.packages.index'));

        $this->assertDatabaseMissing('tour_packages', ['id' => $package->id]);
        $this->assertDatabaseMissing('tour_package_images', ['id' => $galleryImage->id]);
        Storage::disk('public')->assertMissing('tour-packages/cover/cover.jpg');
        Storage::disk('public')->assertMissing('tour-packages/gallery/gallery.jpg');
    }

    public function test_package_index_search_filters_by_title(): void
    {
        $partner = $this->verifiedPartner();
        TourPackage::factory()->create(['user_id' => $partner->id, 'title' => 'Cox Bazar Getaway']);
        TourPackage::factory()->create(['user_id' => $partner->id, 'title' => 'Sylhet Tea Trail']);

        $this->actingAs($partner)
            ->get(route('partner.packages.index', ['search' => 'Cox']))
            ->assertOk()
            ->assertSee('Cox Bazar Getaway')
            ->assertDontSee('Sylhet Tea Trail');
    }

    // --- Traveler (read-only, active packages only) ---------------------

    public function test_traveler_can_browse_active_packages_only(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $partner = $this->verifiedPartner();
        TourPackage::factory()->active()->create(['user_id' => $partner->id, 'title' => 'Visible Active Tour']);
        TourPackage::factory()->inactive()->create(['user_id' => $partner->id, 'title' => 'Hidden Inactive Tour']);

        $this->actingAs($traveler)
            ->get(route('traveler.packages.index'))
            ->assertOk()
            ->assertSee('Visible Active Tour')
            ->assertDontSee('Hidden Inactive Tour');
    }

    public function test_traveler_cannot_view_an_inactive_package(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $partner = $this->verifiedPartner();
        $package = TourPackage::factory()->inactive()->create(['user_id' => $partner->id]);

        $this->actingAs($traveler)
            ->get(route('traveler.packages.show', $package))
            ->assertForbidden();
    }

    // --- Admin (read-only) ----------------------------------------------

    public function test_admin_can_view_all_packages_read_only(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        TourPackage::factory()->create(['user_id' => $partner->id, 'title' => 'Admin Visible Package']);

        $this->actingAs($admin)
            ->get(route('admin.packages.index'))
            ->assertOk()
            ->assertSee('Admin Visible Package');
    }

    public function test_admin_can_view_a_single_package(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        $package = TourPackage::factory()->create(['user_id' => $partner->id, 'title' => 'Admin Detail Package']);

        $this->actingAs($admin)
            ->get(route('admin.packages.show', $package))
            ->assertOk()
            ->assertSee('Admin Detail Package');
    }

    public function test_travel_partner_cannot_access_admin_package_routes(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->get(route('admin.packages.index'))
            ->assertForbidden();
    }
}
