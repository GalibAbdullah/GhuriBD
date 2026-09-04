<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Every Traveler and Travel Partner may open their inbox — the
     * controller scopes the list to conversations they participate in.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVELER->value) || $user->hasRole(UserRole::TRAVEL_PARTNER->value);
    }

    /**
     * Only the two participants may open a conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->hasParticipant($user);
    }

    /**
     * Only Travelers and Travel Partners start conversations with each
     * other — Admins do not chat with providers through this channel.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::TRAVELER->value) || $user->hasRole(UserRole::TRAVEL_PARTNER->value);
    }
}
