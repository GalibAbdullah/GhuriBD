<?php

namespace Tests\Feature;

use App\Enums\ResortAmenity;
use App\Enums\ResortStatus;
use App\Enums\UserRole;
use App\Models\ProviderVerification;
use App\Models\Resort;
use App\Models\ResortImage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResortManagementTest extends TestCase
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
            'name' => 'Sea Pearl Resort',
            'description' => 'A beachfront resort with stunning views.',
            'division' => 'Chattogram',
            'district' => "Cox's Bazar",
            'address' => "Marine Drive Road, Cox's Bazar",
            'contact_phone' => '01700000000',
            'price_range' => '৳3000 - ৳8000',
            'amenities' => [ResortAmenity::WIFI->value, ResortAmenity::SEA_VIEW->value],
        ], $overrides);
    }

    // --- Access control -----------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('partner.resorts.index'))->assertRedirect('/login');
    }

    public function test_traveler_cannot_access_resort_management(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)->get(route('partner.resorts.index'))->assertForbidden();
        $this->actingAs($traveler)->get(route('partner.resorts.create'))->assertForbidden();
        $this->actingAs($traveler)->post(route('partner.resorts.store'), [])->assertForbidden();
    }

    public function test_unverified_travel_partner_is_blocked(): void
    {
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)->get(route('partner.resorts.index'))->assertForbidden();
        $this->actingAs($partner)->get(route('partner.resorts.create'))->assertForbidden();
        $this->actingAs($partner)->post(route('partner.resorts.store'), $this->validPayload())->assertForbidden();
    }

    public function test_admin_is_blocked_from_partner_resort_routes(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);

        $this->actingAs($admin)->get(route('partner.resorts.create'))->assertForbidden();
        $this->actingAs($admin)->put(route('partner.resorts.update', $resort), [])->assertForbidden();
        $this->actingAs($admin)->delete(route('partner.resorts.destroy', $resort))->assertForbidden();
    }

    // --- CRUD for a verified partner -----------------------------------

    public function test_verified_partner_sees_empty_state_with_no_resorts(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->get(route('partner.resorts.index'))
            ->assertOk()
            ->assertSee('My Resorts');
    }

    public function test_verified_partner_can_create_resort_with_cover_and_gallery_images(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();

        $response = $this->actingAs($partner)->post(route('partner.resorts.store'), $this->validPayload([
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            'gallery_images' => [
                UploadedFile::fake()->create('gallery-1.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('gallery-2.jpg', 100, 'image/jpeg'),
            ],
        ]));

        $resort = Resort::where('name', 'Sea Pearl Resort')->firstOrFail();

        $response->assertRedirect(route('partner.resorts.show', $resort))->assertSessionHas('status');

        $this->assertDatabaseHas('resorts', [
            'user_id' => $partner->id,
            'name' => 'Sea Pearl Resort',
            'division' => 'Chattogram',
            'district' => "Cox's Bazar",
            'status' => ResortStatus::ACTIVE->value,
        ]);

        Storage::disk('public')->assertExists($resort->cover_image);
        $this->assertCount(2, $resort->images);
        $resort->images->each(fn (ResortImage $image) => Storage::disk('public')->assertExists($image->image_path));
    }

    public function test_resort_creation_requires_the_core_fields(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->post(route('partner.resorts.store'), [])
            ->assertSessionHasErrors([
                'name', 'description', 'division', 'district',
                'address', 'contact_phone', 'price_range', 'cover_image',
            ]);
    }

    public function test_resort_creation_validates_district_belongs_to_division(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->post(route('partner.resorts.store'), $this->validPayload([
                'division' => 'Sylhet',
                'district' => "Cox's Bazar", // belongs to Chattogram, not Sylhet
                'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors('district');
    }

    public function test_resort_creation_rejects_an_unknown_amenity(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->post(route('partner.resorts.store'), $this->validPayload([
                'amenities' => ['Jacuzzi'],
                'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors('amenities.0');
    }

    public function test_verified_partner_can_view_own_resort(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id, 'name' => 'My Own Resort']);

        $this->actingAs($partner)
            ->get(route('partner.resorts.show', $resort))
            ->assertOk()
            ->assertSee('My Own Resort');
    }

    public function test_partner_cannot_view_another_partners_resort(): void
    {
        $owner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $owner->id]);

        $otherPartner = $this->verifiedPartner();

        $this->actingAs($otherPartner)
            ->get(route('partner.resorts.show', $resort))
            ->assertForbidden();
    }

    public function test_partner_cannot_update_another_partners_resort(): void
    {
        $owner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $owner->id]);

        $otherPartner = $this->verifiedPartner();

        $this->actingAs($otherPartner)
            ->put(route('partner.resorts.update', $resort), $this->validPayload())
            ->assertForbidden();
    }

    public function test_partner_cannot_delete_another_partners_resort(): void
    {
        $owner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $owner->id]);

        $otherPartner = $this->verifiedPartner();

        $this->actingAs($otherPartner)
            ->delete(route('partner.resorts.destroy', $resort))
            ->assertForbidden();

        $this->assertDatabaseHas('resorts', ['id' => $resort->id]);
    }

    public function test_verified_partner_can_update_resort_and_replace_cover_image(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create([
            'user_id' => $partner->id,
            'cover_image' => 'resorts/cover/old.jpg',
        ]);
        Storage::disk('public')->put('resorts/cover/old.jpg', 'old-content');

        $this->actingAs($partner)
            ->put(route('partner.resorts.update', $resort), $this->validPayload([
                'name' => 'Updated Resort Name',
                'cover_image' => UploadedFile::fake()->create('new-cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertRedirect(route('partner.resorts.show', $resort));

        $resort->refresh();

        $this->assertSame('Updated Resort Name', $resort->name);
        Storage::disk('public')->assertMissing('resorts/cover/old.jpg');
        Storage::disk('public')->assertExists($resort->cover_image);
    }

    public function test_updating_resort_can_remove_a_gallery_image(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        $image = ResortImage::factory()->create([
            'resort_id' => $resort->id,
            'image_path' => 'resorts/gallery/keep-me.jpg',
        ]);
        Storage::disk('public')->put($image->image_path, 'gallery-content');

        $this->actingAs($partner)
            ->put(route('partner.resorts.update', $resort), $this->validPayload([
                'remove_gallery_images' => [$image->id],
            ]))
            ->assertRedirect(route('partner.resorts.show', $resort));

        $this->assertDatabaseMissing('resort_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->image_path);
    }

    public function test_deleting_resort_removes_cover_and_gallery_files(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create([
            'user_id' => $partner->id,
            'cover_image' => 'resorts/cover/cover.jpg',
        ]);
        Storage::disk('public')->put($resort->cover_image, 'cover-content');

        $galleryImage = ResortImage::factory()->create([
            'resort_id' => $resort->id,
            'image_path' => 'resorts/gallery/gallery.jpg',
        ]);
        Storage::disk('public')->put($galleryImage->image_path, 'gallery-content');

        $this->actingAs($partner)
            ->delete(route('partner.resorts.destroy', $resort))
            ->assertRedirect(route('partner.resorts.index'));

        $this->assertDatabaseMissing('resorts', ['id' => $resort->id]);
        $this->assertDatabaseMissing('resort_images', ['id' => $galleryImage->id]);
        Storage::disk('public')->assertMissing('resorts/cover/cover.jpg');
        Storage::disk('public')->assertMissing('resorts/gallery/gallery.jpg');
    }

    public function test_resort_index_search_filters_by_name(): void
    {
        $partner = $this->verifiedPartner();
        Resort::factory()->create(['user_id' => $partner->id, 'name' => 'Ocean Breeze Resort']);
        Resort::factory()->create(['user_id' => $partner->id, 'name' => 'Hillside Cottage']);

        $this->actingAs($partner)
            ->get(route('partner.resorts.index', ['search' => 'Ocean']))
            ->assertOk()
            ->assertSee('Ocean Breeze Resort')
            ->assertDontSee('Hillside Cottage');
    }

    // --- Admin (read-only) ----------------------------------------------

    public function test_admin_can_view_all_resorts_read_only(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        Resort::factory()->create(['user_id' => $partner->id, 'name' => 'Admin Visible Resort']);

        $this->actingAs($admin)
            ->get(route('admin.resorts.index'))
            ->assertOk()
            ->assertSee('Admin Visible Resort');
    }

    public function test_admin_can_view_a_single_resort(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id, 'name' => 'Admin Detail Resort']);

        $this->actingAs($admin)
            ->get(route('admin.resorts.show', $resort))
            ->assertOk()
            ->assertSee('Admin Detail Resort');
    }

    public function test_travel_partner_cannot_access_admin_resort_routes(): void
    {
        $partner = $this->verifiedPartner();

        $this->actingAs($partner)
            ->get(route('admin.resorts.index'))
            ->assertForbidden();
    }
}
