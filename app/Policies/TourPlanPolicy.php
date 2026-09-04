<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TourPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TourPlanPolicy
{
    public function create(User $user): Response
    {
        return $user->hasRole(UserRole::TRAVELER->value)
            ? Response::allow()
            : Response::denyWithStatus(403, 'Only Travelers can use the AI Tour Planner.');
    }

    public function view(User $user, TourPlan $plan): Response
    {
        return $user->id === $plan->traveler_id
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function update(User $user, TourPlan $plan): Response
    {
        return $this->view($user, $plan);
    }

    public function delete(User $user, TourPlan $plan): Response
    {
        return $this->view($user, $plan);
    }
}
