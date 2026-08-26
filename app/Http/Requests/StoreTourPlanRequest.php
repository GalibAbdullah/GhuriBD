<?php

namespace App\Http\Requests;

use App\Enums\Interest;
use App\Models\GuideAvailability;
use App\Models\TourPlan;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreTourPlanRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('create', TourPlan::class);
    }

    public function rules(): array
    {
        $rules = config('ghuribd.tour_planner');

        return [
            'destination' => ['required', 'string', 'max:120'],
            'start_date' => ['nullable', 'date', 'after_or_equal:'.GuideAvailability::today()->toDateString()],
            'days' => ['required', 'integer', 'min:'.$rules['min_days'], 'max:'.$rules['max_days']],
            'budget' => ['required', 'numeric', 'min:'.$rules['min_budget'], 'max:'.$rules['max_budget']],
            'interests' => ['required', 'array', 'min:1'],
            'interests.*' => [Rule::in(Interest::values())],
        ];
    }

    public function messages(): array
    {
        $rules = config('ghuribd.tour_planner');

        return [
            'start_date.after_or_equal' => 'Your trip cannot start in the past.',
            'days.min' => "A plan must cover at least {$rules['min_days']} day.",
            'days.max' => "A single plan cannot exceed {$rules['max_days']} days.",
            'budget.min' => "Please set a budget of at least {$rules['min_budget']}.",
            'interests.required' => 'Pick at least one interest so we can shape your itinerary.',
            'interests.min' => 'Pick at least one interest so we can shape your itinerary.',
        ];
    }
}
