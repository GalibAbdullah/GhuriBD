<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Spatie\Permission\Middleware\RoleMiddleware;

class TravelPartnerMiddleware extends RoleMiddleware
{
    public function handle($request, Closure $next, $role = null, $guard = null)
    {
        return parent::handle($request, $next, UserRole::TRAVEL_PARTNER->value, $guard);
    }
}