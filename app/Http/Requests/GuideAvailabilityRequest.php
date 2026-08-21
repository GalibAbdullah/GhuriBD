<?php

namespace App\Http\Requests;

use App\Enums\AvailabilityStatus;
use App\Models\GuideAvailability;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class GuideAvailabilityRequest extends FormRequest
{
    /**
     * Authorize here rather than in the controller: form request validation runs
     * first, so a locked slot would otherwise answer with a field error instead
     * of the policy's 403.
     */
    public function authorize(): Response
    {
        $slot = $this->slot();

        return $slot === null
            ? Gate::inspect('create', GuideAvailability::class)
            : Gate::inspect('update', $slot);
    }

    /**
     * The slot being edited, or null when creating.
     */
    public function slot(): ?GuideAvailability
    {
        $slot = $this->route('availability');

        return $slot instanceof GuideAvailability ? $slot : null;
    }

    protected function prepareForValidation(): void
    {
        $notes = $this->input('notes');
        $price = $this->input('price');

        $this->merge([
            'start_time' => GuideAvailability::normalizeTime($this->input('start_time')),
            'end_time' => GuideAvailability::normalizeTime($this->input('end_time')),
            // "1,500" is a natural thing to type for a price; numeric would reject it.
            'price' => is_string($price) ? str_replace(',', '', trim($price)) : $price,
            'notes' => is_string($notes) && trim($notes) === '' ? null : $notes,
        ]);
    }

    public function rules(): array
    {
        $rules = config('ghuribd.availability');
        $today = GuideAvailability::today();

        return [
            'available_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.$today->toDateString(),
                'before_or_equal:'.$today->copy()->addDays($rules['max_advance_days'])->toDateString(),
            ],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s'],
            'capacity' => ['required', 'integer', 'min:'.$rules['min_capacity'], 'max:'.$rules['max_capacity']],
            'price' => ['required', 'numeric', 'min:0', 'max:'.$rules['max_price']],
            'status' => ['required', 'string', 'in:'.implode(',', AvailabilityStatus::guideAssignableValues())],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateTimeWindow($validator),
            fn (Validator $validator) => $this->validateNotAlreadyStarted($validator),
            fn (Validator $validator) => $this->validateNoOverlap($validator),
        ];
    }

    /**
     * end_time must be after start_time, and the resulting window must sit
     * inside the configured duration bounds.
     */
    private function validateTimeWindow(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['start_time', 'end_time'])) {
            return;
        }

        $rules = config('ghuribd.availability');
        $start = Carbon::createFromFormat('H:i:s', $this->input('start_time'));
        $end = Carbon::createFromFormat('H:i:s', $this->input('end_time'));

        if ($end->lessThanOrEqualTo($start)) {
            $validator->errors()->add('end_time', 'The end time must be later than the start time.');

            return;
        }

        $minutes = (int) round($start->diffInMinutes($end));

        if ($minutes < $rules['min_duration_minutes']) {
            $validator->errors()->add('end_time', "A slot must be at least {$rules['min_duration_minutes']} minutes long.");
        }

        if ($minutes > $rules['max_duration_minutes']) {
            $validator->errors()->add('end_time', "A slot may not be longer than {$rules['max_duration_minutes']} minutes.");
        }
    }

    /**
     * A slot dated today must still be in the future. "after_or_equal:today"
     * accepts today, which would otherwise allow publishing a 09:00 slot at 14:00.
     */
    private function validateNotAlreadyStarted(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['available_date', 'start_time'])) {
            return;
        }

        $startsAt = Carbon::parse(
            $this->input('available_date').' '.$this->input('start_time'),
            GuideAvailability::timezone(),
        );

        if ($startsAt->isPast()) {
            $validator->errors()->add('start_time', 'That start time has already passed today. Choose a later time.');
        }
    }

    /**
     * A guide cannot be in two places at once, so a new window may not overlap
     * an existing one on the same date.
     */
    private function validateNoOverlap(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['available_date', 'start_time', 'end_time'])) {
            return;
        }

        $slot = $this->slot();

        $conflict = GuideAvailability::query()
            ->forGuide($this->user())
            ->overlapping(
                $this->input('available_date'),
                $this->input('start_time'),
                $this->input('end_time'),
            )
            ->when($slot, fn ($query) => $query->whereKeyNot($slot->getKey()))
            ->first();

        if ($conflict !== null) {
            $validator->errors()->add('start_time', sprintf(
                'This overlaps an existing slot on that date (%s–%s).',
                Carbon::createFromFormat('H:i:s', $conflict->start_time)->format('g:i A'),
                Carbon::createFromFormat('H:i:s', $conflict->end_time)->format('g:i A'),
            ));
        }
    }

    public function messages(): array
    {
        $rules = config('ghuribd.availability');

        return [
            'available_date.required' => 'Please choose a date.',
            'available_date.date_format' => 'Please choose a valid date.',
            'available_date.after_or_equal' => 'You cannot publish availability for a past date.',
            'available_date.before_or_equal' => "You can only publish availability up to {$rules['max_advance_days']} days ahead.",
            'start_time.required' => 'Please choose a start time.',
            'start_time.date_format' => 'Please choose a valid start time.',
            'end_time.required' => 'Please choose an end time.',
            'end_time.date_format' => 'Please choose a valid end time.',
            'capacity.required' => 'Please set how many travelers can book this slot.',
            'capacity.min' => "Capacity must be at least {$rules['min_capacity']}.",
            'capacity.max' => "Capacity cannot exceed {$rules['max_capacity']}.",
            'price.required' => 'Please set a price for this slot.',
            'price.numeric' => 'The price must be a number.',
            'price.max' => 'That price is unrealistically high.',
            'status.in' => 'Status must be either Available or Blocked.',
            'notes.max' => 'Notes may not exceed 500 characters.',
        ];
    }
}
