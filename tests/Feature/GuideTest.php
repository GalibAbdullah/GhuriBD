<?php

namespace Tests\Feature;

use App\Enums\ProviderType;
use App\Enums\UserRole;
use App\Models\GuideAvailability;
use App\Models\ProviderVerification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideTest extends TestCase
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

    private function traveler(): User
    {
        return $this->userWithRole(UserRole::TRAVELER->value);
    }

    private function verifiedGuide(array $attributes = []): User
    {
        $guide = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        ProviderVerification::factory()->approved()->create(array_merge([
            'user_id' => $guide->id,
            'provider_type' => ProviderType::TOUR_GUIDE->value,
            'provider_name' => 'Sundarban Trails',
            'business_address' => "Khulna, Bangladesh",
        ], $attributes));

        return $guide;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('guides.index'))->assertRedirect('/login');
    }

    public function test_index_lists_only_verified_tour_guides(): void
    {
        $guide = $this->verifiedGuide();
        $unverifiedPartner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        $pendingGuide = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        ProviderVerification::factory()->create([
            'user_id' => $pendingGuide->id,
            'provider_type' => ProviderType::TOUR_GUIDE->value,
        ]);

        $response = $this->actingAs($this->traveler())->get(route('guides.index'));

        $response->assertOk();
        $response->assertSee('Sundarban Trails');
        $response->assertDontSee($unverifiedPartner->name);
        $response->assertDontSee($pendingGuide->name);
    }

    public function test_index_search_filters_by_provider_name_or_location(): void
    {
        $this->verifiedGuide(['provider_name' => 'Sundarban Trails', 'business_address' => 'Khulna']);
        $this->verifiedGuide(['provider_name' => 'Sylhet Hills Explorer', 'business_address' => 'Sylhet']);

        $response = $this->actingAs($this->traveler())->get(route('guides.index', ['search' => 'Sylhet']));

        $response->assertSee('Sylhet Hills Explorer');
        $response->assertDontSee('Sundarban Trails');
    }

    public function test_show_displays_upcoming_bookable_slots_only(): void
    {
        $guide = $this->verifiedGuide();

        $bookable = GuideAvailability::factory()->create([
            'user_id' => $guide->id,
            'available_date' => now()->addDays(3)->toDateString(),
            'price' => 2500,
        ]);

        GuideAvailability::factory()->past()->create(['user_id' => $guide->id]);
        GuideAvailability::factory()->blocked()->create(['user_id' => $guide->id]);

        $response = $this->actingAs($this->traveler())->get(route('guides.show', $guide));

        $response->assertOk();
        $response->assertViewHas('availabilities', function ($availabilities) use ($bookable) {
            return $availabilities->count() === 1 && $availabilities->first()->is($bookable);
        });
    }

    public function test_show_returns_404_for_a_partner_who_is_not_a_verified_guide(): void
    {
        $resortOwner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);
        ProviderVerification::factory()->approved()->create([
            'user_id' => $resortOwner->id,
            'provider_type' => ProviderType::RESORT_OWNER->value,
        ]);

        $this->actingAs($this->traveler())
            ->get(route('guides.show', $resortOwner))
            ->assertNotFound();
    }

    public function test_a_traveler_can_message_a_guide_from_the_profile_page(): void
    {
        $guide = $this->verifiedGuide();
        $traveler = $this->traveler();

        $this->actingAs($traveler)
            ->post(route('messages.store'), [
                'recipient_id' => $guide->id,
                'body' => 'Hi! Is your slot still open?',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('conversations', [
            'traveler_id' => $traveler->id,
            'partner_id' => $guide->id,
        ]);
    }

    public function test_a_travel_partner_does_not_see_a_message_form_on_a_guide_profile(): void
    {
        $guide = $this->verifiedGuide();
        $otherPartner = $this->userWithRole(UserRole::TRAVEL_PARTNER->value);

        $this->actingAs($otherPartner)
            ->get(route('guides.show', $guide))
            ->assertDontSee('Send Message');
    }
}
