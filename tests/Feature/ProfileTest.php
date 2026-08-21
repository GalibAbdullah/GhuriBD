<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_their_profile(): void
    {
        $user = User::factory()->create([
            'phone' => '01700000000',
            'gender' => 'Male',
            'address' => 'Dhaka',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->email);
    }

    public function test_guest_cannot_access_profile_pages(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/profile/edit')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_edit_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile/edit')
            ->assertOk()
            ->assertSee('Edit Profile')
            ->assertSee('Change Password');
    }

    public function test_user_can_update_their_own_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Updated Name',
                'phone' => '01811111111',
                'date_of_birth' => '1995-05-15',
                'gender' => 'Female',
                'address' => 'Chattogram',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '01811111111',
            'date_of_birth' => '1995-05-15 00:00:00',
            'gender' => 'Female',
            'address' => 'Chattogram',
        ]);
    }

    public function test_user_cannot_change_email_via_profile_update(): void
    {
        $user = User::factory()->create(['email' => 'original@example.com']);

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Updated Name',
                'email' => 'hacked@example.com',
            ])
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'original@example.com',
        ]);
    }

    public function test_profile_update_requires_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name' => '',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_profile_update_rejects_invalid_gender(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Valid Name',
                'gender' => 'Invalid',
            ])
            ->assertSessionHasErrors('gender');
    }

    public function test_profile_update_rejects_future_date_of_birth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name' => 'Valid Name',
                'date_of_birth' => now()->addDay()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'profile_photo' => UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->profile_photo);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    public function test_profile_photo_rejects_non_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'profile_photo' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertSessionHasErrors('profile_photo');
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_password_change_rejects_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_password_change_requires_confirmation_match(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'old-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'different-password',
            ])
            ->assertSessionHasErrors('password');
    }
}