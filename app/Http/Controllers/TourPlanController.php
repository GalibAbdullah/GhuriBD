<?php

namespace App\Http\Controllers;

use App\Enums\Interest;
use App\Http\Requests\StoreTourPlanRequest;
use App\Models\TourPlan;
use App\Planning\TourPlanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TourPlanController extends Controller
{
    public function index(Request $request): View
    {
        $plans = TourPlan::query()
            ->where('traveler_id', $request->user()->id)
            ->withCount('days')
            ->latest()
            ->paginate(10);

        return view('tour-plans.index', ['plans' => $plans]);
    }

    public function create(): View
    {
        Gate::authorize('create', TourPlan::class);

        return view('tour-plans.create', ['interests' => Interest::cases()]);
    }

    public function store(StoreTourPlanRequest $request, TourPlanner $planner): RedirectResponse
    {
        $data = $request->validated();

        $plan = TourPlan::create([
            'traveler_id' => $request->user()->id,
            'destination' => $data['destination'],
            'start_date' => $data['start_date'] ?? null,
            'duration_days' => $data['days'],
            'budget' => $data['budget'],
            'interests' => $data['interests'],
        ]);

        $this->generateDays($plan, $planner);

        return redirect()->route('tour-plans.show', $plan);
    }

    public function show(TourPlan $plan): View
    {
        Gate::authorize('view', $plan);

        return view('tour-plans.show', [
            'plan' => $plan->load('days.suggestedAvailability.guide'),
        ]);
    }

    public function regenerate(TourPlan $plan, TourPlanner $planner): RedirectResponse
    {
        Gate::authorize('update', $plan);

        DB::transaction(function () use ($plan, $planner): void {
            $plan->days()->delete();
            $this->generateDays($plan, $planner);
            $plan->update(['regenerated_at' => now()]);
        });

        return redirect()
            ->route('tour-plans.show', $plan)
            ->with('status', 'Itinerary refreshed against the latest availability.');
    }

    public function destroy(TourPlan $plan): RedirectResponse
    {
        Gate::authorize('delete', $plan);

        $plan->delete();

        return redirect()->route('tour-plans.index')->with('status', 'Tour plan deleted.');
    }

    private function generateDays(TourPlan $plan, TourPlanner $planner): void
    {
        foreach ($planner->generate($plan) as $day) {
            $plan->days()->create([
                'day_number' => $day->dayNumber,
                'title' => $day->title,
                'theme' => $day->theme,
                'budget_allocated' => $day->budgetAllocated,
                'description' => $day->description,
                'suggested_availability_id' => $day->suggestedAvailabilityId,
            ]);
        }
    }
}
