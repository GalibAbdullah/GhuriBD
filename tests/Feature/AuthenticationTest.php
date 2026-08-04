<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_auth_pages(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_user_can_register_and_is_authenticated(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => UserRole::TRAVELER->value,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/traveler');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_can_login_and_logout(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
        ]);
        $user->assignRole(UserRole::TRAVELER->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'remember' => 'on',
        ])->assertSessionHasErrors('email')->assertSessionHasInput('remember', 'on');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123!',
            'remember' => 'on',
        ])->assertRedirect('/traveler');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_user_can_request_and_complete_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect('/login');

        $this->assertTrue(password_verify('NewPassword123!', $user->fresh()->password));
    }
}