<?php

namespace App\Http\Controllers;

use App\Models\GuideAvailability;
use App\Models\User;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A minimal public entry point into booking a guide, ahead of the full
 * marketplace/search experience. Other Sprint 3 work (destination search,
 * the Tour Guide Marketplace) is expected to link here once it exists.
 */
class GuidePublicController extends Controller
{
    public function show(User $guide): View
    {
        if (! $guide->isVerifiedTourGuide()) {
            throw new NotFoundHttpException;
        }

        $slots = GuideAvailability::query()
            ->forGuide($guide)
            ->bookable()
            ->orderBy('available_date')
            ->orderBy('start_time')
            ->paginate(10);

        return view('guides.show', [
            'guide' => $guide,
            'slots' => $slots,
        ]);
    }
}
