<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeder_creates_three_roles(): void
    {
        $this->seed(RoleSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => UserRole::TRAVELER->value]);
        $this->assertDatabaseHas('roles', ['name' => UserRole::TRAVEL_PARTNER->value]);
        $this->assertDatabaseHas('roles', ['name' => UserRole::ADMIN->value]);
    }

    public function test_admin_seeder_creates_one_admin_user(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(AdminSeeder::class);

        $admin = User::where('email', config('ghuribd.admin.email'))->firstOrFail();

        $this->assertTrue($admin->hasRole(UserRole::ADMIN->value));
        $this->assertSame(config('ghuribd.admin.name'), $admin->name);
    }

    public function test_registration_assigns_traveler_role(): void
    {
        $this->seed(RoleSeeder::class);

        $this->post('/register', [
            'name' => 'Traveler User',
            'email' => 'traveler@example.com',
            'role' => UserRole::TRAVELER->value,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/traveler');

        $user = User::where('email', 'traveler@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(UserRole::TRAVELER->value));
    }

    public function test_registration_assigns_travel_partner_role(): void
    {
        $this->seed(RoleSeeder::class);

        $this->post('/register', [
            'name' => 'Partner User',
            'email' => 'partner@example.com',
            'role' => UserRole::TRAVEL_PARTNER->value,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect('/partner');

        $user = User::where('email', 'partner@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole(UserRole::TRAVEL_PARTNER->value));
    }

    public function test_registration_rejects_admin_role(): void
    {
        $this->seed(RoleSeeder::class);

        $this->post('/register', [
            'name' => 'Invalid Admin',
            'email' => 'invalid-admin@example.com',
            'role' => UserRole::ADMIN->value,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('role');
    }

    public function test_login_redirects_by_role(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = $this->userWithRole(UserRole::ADMIN->value, 'admin@example.com');
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value, 'partner@example.com');
        $traveler = $this->userWithRole(UserRole::TRAVELER->value, 'traveler@example.com');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'Password123!',
        ])->assertRedirect('/admin');

        $this->post('/logout');

        $this->post('/login', [
            'email' => $partner->email,
            'password' => 'Password123!',
        ])->assertRedirect('/partner');

        $this->post('/logout');

        $this->post('/login', [
            'email' => $traveler->email,
            'password' => 'Password123!',
        ])->assertRedirect('/traveler');
    }

    public function test_role_middleware_returns_403_for_other_roles(): void
    {
        $this->seed(RoleSeeder::class);

        $traveler = $this->userWithRole(UserRole::TRAVELER->value, 'traveler@example.com');
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value, 'partner@example.com');
        $admin = $this->userWithRole(UserRole::ADMIN->value, 'admin@example.com');

        $this->actingAs($traveler)->get('/partner')->assertForbidden();
        $this->actingAs($traveler)->get('/admin')->assertForbidden();

        $this->actingAs($partner)->get('/traveler')->assertForbidden();
        $this->actingAs($partner)->get('/admin')->assertForbidden();

        $this->actingAs($admin)->get('/traveler')->assertForbidden();
        $this->actingAs($admin)->get('/partner')->assertForbidden();
    }

    private function userWithRole(string $role, string $email): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'password' => 'Password123!',
        ]);

        $user->assignRole($role);

        return $user;
    }
}