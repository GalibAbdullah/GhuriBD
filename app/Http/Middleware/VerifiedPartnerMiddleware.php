<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedPartnerMiddleware
{
    /**
     * Only let Travel Partners with an approved provider verification through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isVerifiedProvider(), 403);

        return $next($request);
    }
}
