<?php

namespace Tests\Feature;

use App\Enums\RoomAmenity;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\ProviderVerification;
use App\Models\Resort;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoomManagementTest extends TestCase
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
            'room_name' => 'Ocean View Deluxe',
            'room_type' => 'Deluxe Room',
            'description' => 'A spacious room with a stunning ocean view.',
            'price_per_night' => 5000,
            'capacity' => 2,
            'total_rooms' => 5,
            'available_rooms' => 3,
            'bed_type' => 'King',
            'room_size' => '350 sq ft',
            'amenities' => [RoomAmenity::AC->value, RoomAmenity::SEA_VIEW->value],
            'status' => RoomStatus::AVAILABLE->value,
        ], $overrides);
    }

    // --- Access control -----------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);

        $this->get(route('partner.resorts.rooms.index', $resort))->assertRedirect('/login');
    }

    public function test_traveler_cannot_access_room_management(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)->get(route('partner.resorts.rooms.index', $resort))->assertForbidden();
        $this->actingAs($traveler)->get(route('partner.resorts.rooms.create', $resort))->assertForbidden();
        $this->actingAs($traveler)->post(route('partner.resorts.rooms.store', $resort), [])->assertForbidden();
    }

    public function test_unverified_travel_partner_is_blocked(): void
    {
        $owner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $owner->id]);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($partner)->get(route('partner.resorts.rooms.index', $resort))->assertForbidden();
        $this->actingAs($partner)->get(route('partner.resorts.rooms.create', $resort))->assertForbidden();
        $this->actingAs($partner)->post(route('partner.resorts.rooms.store', $resort), $this->validPayload())->assertForbidden();
    }

    // --- CRUD for a verified partner -----------------------------------

    public function test_verified_partner_sees_empty_state_with_no_rooms(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);

        $this->actingAs($partner)
            ->get(route('partner.resorts.rooms.index', $resort))
            ->assertOk()
            ->assertSee('No rooms added yet.');
    }

    public function test_verified_partner_can_create_room_with_cover_and_gallery_images(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);

        $response = $this->actingAs($partner)->post(route('partner.resorts.rooms.store', $resort), $this->validPayload([
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            'gallery_images' => [
                UploadedFile::fake()->create('gallery-1.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('gallery-2.jpg', 100, 'image/jpeg'),
            ],
        ]));

        $room = Room::where('room_name', 'Ocean View Deluxe')->firstOrFail();

        $response->assertRedirect(route('partner.resorts.rooms.show', [$resort, $room]))->assertSessionHas('status');

        $this->assertDatabaseHas('rooms', [
            'resort_id' => $resort->id,
            'room_name' => 'Ocean View Deluxe',
            'room_type' => 'Deluxe Room',
            'status' => RoomStatus::AVAILABLE->value,
        ]);

        Storage::disk('public')->assertExists($room->cover_image);
        $this->assertCount(2, $room->images);
        $room->images->each(fn (RoomImage $image) => Storage::disk('public')->assertExists($image->image_path));
    }

    public function test_room_creation_requires_the_core_fields(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);

        $this->actingAs($partner)
            ->post(route('partner.resorts.rooms.store', $resort), [])
            ->assertSessionHasErrors([
                'room_name', 'room_type', 'description', 'price_per_night',
                'capacity', 'total_rooms', 'available_rooms', 'bed_type', 'room_size', 'cover_image',
            ]);
    }

    public function test_room_creation_rejects_available_rooms_exceeding_total_rooms(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);

        $this->actingAs($partner)
            ->post(route('partner.resorts.rooms.store', $resort), $this->validPayload([
                'total_rooms' => 3,
                'available_rooms' => 5,
                'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors('available_rooms');
    }

    public function test_room_creation_rejects_an_unknown_amenity(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);

        $this->actingAs($partner)
            ->post(route('partner.resorts.rooms.store', $resort), $this->validPayload([
                'amenities' => ['Jacuzzi'],
                'cover_image' => UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors('amenities.0');
    }

    public function test_verified_partner_can_view_a_room_of_their_own_resort(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        $room = Room::factory()->create(['resort_id' => $resort->id, 'room_name' => 'My Own Room']);

        $this->actingAs($partner)
            ->get(route('partner.resorts.rooms.show', [$resort, $room]))
            ->assertOk()
            ->assertSee('My Own Room');
    }

    public function test_partner_cannot_manage_rooms_of_another_partners_resort(): void
    {
        $owner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $owner->id]);
        $room = Room::factory()->create(['resort_id' => $resort->id]);

        $otherPartner = $this->verifiedPartner();

        $this->actingAs($otherPartner)->get(route('partner.resorts.rooms.index', $resort))->assertForbidden();
        $this->actingAs($otherPartner)->get(route('partner.resorts.rooms.create', $resort))->assertForbidden();
        $this->actingAs($otherPartner)->get(route('partner.resorts.rooms.show', [$resort, $room]))->assertForbidden();
        $this->actingAs($otherPartner)->put(route('partner.resorts.rooms.update', [$resort, $room]), $this->validPayload())->assertForbidden();
        $this->actingAs($otherPartner)->delete(route('partner.resorts.rooms.destroy', [$resort, $room]))->assertForbidden();

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_room_ids_cannot_be_accessed_through_a_resort_they_do_not_belong_to(): void
    {
        $partner = $this->verifiedPartner();
        $resortA = Resort::factory()->create(['user_id' => $partner->id]);
        $resortB = Resort::factory()->create(['user_id' => $partner->id]);
        $room = Room::factory()->create(['resort_id' => $resortA->id]);

        $this->actingAs($partner)
            ->get(route('partner.resorts.rooms.show', [$resortB, $room]))
            ->assertNotFound();
    }

    public function test_verified_partner_can_update_room_and_replace_cover_image(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        $room = Room::factory()->create([
            'resort_id' => $resort->id,
            'cover_image' => 'rooms/cover/old.jpg',
        ]);
        Storage::disk('public')->put('rooms/cover/old.jpg', 'old-content');

        $this->actingAs($partner)
            ->put(route('partner.resorts.rooms.update', [$resort, $room]), $this->validPayload([
                'room_name' => 'Updated Room Name',
                'cover_image' => UploadedFile::fake()->create('new-cover.jpg', 100, 'image/jpeg'),
            ]))
            ->assertRedirect(route('partner.resorts.rooms.show', [$resort, $room]));

        $room->refresh();

        $this->assertSame('Updated Room Name', $room->room_name);
        Storage::disk('public')->assertMissing('rooms/cover/old.jpg');
        Storage::disk('public')->assertExists($room->cover_image);
    }

    public function test_updating_room_can_remove_a_gallery_image(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        $room = Room::factory()->create(['resort_id' => $resort->id]);
        $image = RoomImage::factory()->create([
            'room_id' => $room->id,
            'image_path' => 'rooms/gallery/keep-me.jpg',
        ]);
        Storage::disk('public')->put($image->image_path, 'gallery-content');

        $this->actingAs($partner)
            ->put(route('partner.resorts.rooms.update', [$resort, $room]), $this->validPayload([
                'remove_gallery_images' => [$image->id],
            ]))
            ->assertRedirect(route('partner.resorts.rooms.show', [$resort, $room]));

        $this->assertDatabaseMissing('room_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->image_path);
    }

    public function test_deleting_room_removes_cover_and_gallery_files(): void
    {
        Storage::fake('public');

        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        $room = Room::factory()->create([
            'resort_id' => $resort->id,
            'cover_image' => 'rooms/cover/cover.jpg',
        ]);
        Storage::disk('public')->put($room->cover_image, 'cover-content');

        $galleryImage = RoomImage::factory()->create([
            'room_id' => $room->id,
            'image_path' => 'rooms/gallery/gallery.jpg',
        ]);
        Storage::disk('public')->put($galleryImage->image_path, 'gallery-content');

        $this->actingAs($partner)
            ->delete(route('partner.resorts.rooms.destroy', [$resort, $room]))
            ->assertRedirect(route('partner.resorts.rooms.index', $resort));

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
        $this->assertDatabaseMissing('room_images', ['id' => $galleryImage->id]);
        Storage::disk('public')->assertMissing('rooms/cover/cover.jpg');
        Storage::disk('public')->assertMissing('rooms/gallery/gallery.jpg');
    }

    public function test_room_index_search_filters_by_name(): void
    {
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        Room::factory()->create(['resort_id' => $resort->id, 'room_name' => 'Ocean Breeze Room']);
        Room::factory()->create(['resort_id' => $resort->id, 'room_name' => 'Hillside Cottage']);

        $this->actingAs($partner)
            ->get(route('partner.resorts.rooms.index', [$resort, 'search' => 'Ocean']))
            ->assertOk()
            ->assertSee('Ocean Breeze Room')
            ->assertDontSee('Hillside Cottage');
    }

    // --- Admin (read-only) ----------------------------------------------

    public function test_admin_can_view_rooms_of_any_resort_read_only(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        Room::factory()->create(['resort_id' => $resort->id, 'room_name' => 'Admin Visible Room']);

        $this->actingAs($admin)
            ->get(route('admin.resorts.rooms.index', $resort))
            ->assertOk()
            ->assertSee('Admin Visible Room');
    }

    public function test_admin_can_view_a_single_room(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        $room = Room::factory()->create(['resort_id' => $resort->id, 'room_name' => 'Admin Detail Room']);

        $this->actingAs($admin)
            ->get(route('admin.resorts.rooms.show', [$resort, $room]))
            ->assertOk()
            ->assertSee('Admin Detail Room');
    }

    public function test_admin_cannot_create_edit_or_delete_rooms(): void
    {
        $admin = $this->userWithRole(UserRole::ADMIN->value);
        $partner = $this->verifiedPartner();
        $resort = Resort::factory()->create(['user_id' => $partner->id]);
        $room = Room::factory()->create(['resort_id' => $resort->id]);

        $this->actingAs($admin)->get(route('partner.resorts.rooms.create', $resort))->assertForbidden();
        $this->actingAs($admin)->post(route('partner.resorts.rooms.store', $resort), $this->validPayload())->assertForbidden();
        $this->actingAs($admin)->put(route('partner.resorts.rooms.update', [$resort, $room]), $this->validPayload())->assertForbidden();
        $this->actingAs($admin)->delete(route('partner.resorts.rooms.destroy', [$resort, $room]))->assertForbidden();
    }
}
