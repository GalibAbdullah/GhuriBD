<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }
}