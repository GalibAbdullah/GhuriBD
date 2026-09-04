<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\User;
use App\Notifications\NewMessageReceived;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MessageTest extends TestCase
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

    public function test_a_traveler_can_start_a_conversation_with_a_partner(): void
    {
        Notification::fake();

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $response = $this->actingAs($traveler)->post(route('messages.store'), [
            'recipient_id' => $partner->id,
            'body' => 'Hi, is this resort available next week?',
        ]);

        $conversation = Conversation::sole();
        $response->assertRedirect(route('messages.show', $conversation));

        $this->assertEquals($traveler->id, $conversation->traveler_id);
        $this->assertEquals($partner->id, $conversation->partner_id);
        $this->assertEquals(1, $conversation->messages()->count());

        Notification::assertSentTo($partner, NewMessageReceived::class);
    }

    public function test_starting_a_conversation_twice_reuses_the_thread(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($traveler)->post(route('messages.store'), [
            'recipient_id' => $partner->id,
            'body' => 'Hello!',
        ]);

        $this->actingAs($traveler)->post(route('messages.store'), [
            'recipient_id' => $partner->id,
            'body' => 'Hello!',
        ]);

        $this->assertEquals(1, Conversation::count());
        $this->assertEquals(1, Conversation::sole()->messages()->count());
    }

    public function test_a_partner_can_reply_and_it_notifies_the_traveler(): void
    {
        Notification::fake();

        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $conversation = Conversation::factory()->create([
            'traveler_id' => $traveler->id,
            'partner_id' => $partner->id,
        ]);

        $this->actingAs($partner)
            ->post(route('messages.reply', $conversation), ['body' => 'Yes, it is available.'])
            ->assertRedirect(route('messages.show', $conversation));

        $this->assertEquals(1, $conversation->messages()->count());
        Notification::assertSentTo($traveler, NewMessageReceived::class);
    }

    public function test_viewing_a_conversation_marks_the_other_sides_messages_read(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $conversation = Conversation::factory()->create([
            'traveler_id' => $traveler->id,
            'partner_id' => $partner->id,
        ]);
        $conversation->messages()->create(['sender_id' => $partner->id, 'body' => 'Hi there']);

        $this->assertEquals(1, $conversation->unreadCountFor($traveler));

        $this->actingAs($traveler)->get(route('messages.show', $conversation));

        $this->assertEquals(0, $conversation->unreadCountFor($traveler));
    }

    public function test_a_user_outside_the_conversation_cannot_view_it(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $partner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $stranger = $this->userWithRole(UserRole::TRAVELER->value);

        $conversation = Conversation::factory()->create([
            'traveler_id' => $traveler->id,
            'partner_id' => $partner->id,
        ]);

        $this->actingAs($stranger)
            ->get(route('messages.show', $conversation))
            ->assertForbidden();
    }

    public function test_a_traveler_cannot_start_a_conversation_with_another_traveler(): void
    {
        $traveler = $this->userWithRole(UserRole::TRAVELER->value);
        $otherTraveler = $this->userWithRole(UserRole::TRAVELER->value);

        $this->actingAs($traveler)
            ->post(route('messages.store'), [
                'recipient_id' => $otherTraveler->id,
                'body' => 'Hey',
            ])
            ->assertStatus(422);
    }
}
