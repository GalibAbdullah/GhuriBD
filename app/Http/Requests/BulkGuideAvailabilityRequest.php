<?php

namespace App\Http\Requests;

use App\Enums\AvailabilityStatus;
use App\Models\GuideAvailability;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class BulkGuideAvailabilityRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('create', GuideAvailability::class);
    }

    protected function prepareForValidation(): void
    {
        $price = $this->input('price');

        $this->merge([
            'start_time' => GuideAvailability::normalizeTime($this->input('start_time')),
            'end_time' => GuideAvailability::normalizeTime($this->input('end_time')),
            'price' => is_string($price) ? str_replace(',', '', trim($price)) : $price,
        ]);
    }

    public function rules(): array
    {
        $rules = config('ghuribd.availability');
        $today = GuideAvailability::today();
        $horizon = $today->copy()->addDays($rules['max_advance_days'])->toDateString();

        return [
            'start_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:'.$today->toDateString(),
                'before_or_equal:'.$horizon,
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
                'before_or_equal:'.$horizon,
            ],
            'weekdays' => ['required', 'array', 'min:1', 'max:7'],
            'weekdays.*' => ['integer', 'between:0,6'],
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
            fn (Validator $validator) => $this->validateRangeLength($validator),
            fn (Validator $validator) => $this->validateRangeMatchesWeekdays($validator),
        ];
    }

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
     * Caps how much work one submission can queue up.
     */
    private function validateRangeLength(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['start_date', 'end_date'])) {
            return;
        }

        $max = config('ghuribd.availability.max_bulk_range_days');
        $days = (int) round($this->startDate()->diffInDays($this->endDate())) + 1;

        if ($days > $max) {
            $validator->errors()->add('end_date', "A single bulk publish may cover at most {$max} days.");
        }
    }

    /**
     * Rejects a range that contains none of the selected weekdays — e.g. "only
     * Mondays" across a Tue–Thu range would otherwise create nothing silently.
     */
    private function validateRangeMatchesWeekdays(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['start_date', 'end_date', 'weekdays', 'start_time'])) {
            return;
        }

        if ($this->matchingDates() === []) {
            $validator->errors()->add('weekdays', 'No dates in that range fall on the selected days of the week.');
        }
    }

    public function startDate(): Carbon
    {
        return Carbon::parse($this->input('start_date'), GuideAvailability::timezone())->startOfDay();
    }

    public function endDate(): Carbon
    {
        return Carbon::parse($this->input('end_date'), GuideAvailability::timezone())->startOfDay();
    }

    /**
     * Every date in the range that falls on a selected weekday and has not
     * already started.
     *
     * @return array<int, string>
     */
    public function matchingDates(): array
    {
        $weekdays = array_map('intval', (array) $this->input('weekdays', []));
        $startsAtTime = $this->input('start_time');
        $timezone = GuideAvailability::timezone();

        $dates = [];

        for ($date = $this->startDate(); $date->lessThanOrEqualTo($this->endDate()); $date->addDay()) {
            if (! in_array($date->dayOfWeek, $weekdays, true)) {
                continue;
            }

            // The first day of the range can be today, whose earlier hours are gone.
            if (Carbon::parse($date->toDateString().' '.$startsAtTime, $timezone)->isPast()) {
                continue;
            }

            $dates[] = $date->toDateString();
        }

        return $dates;
    }

    public function messages(): array
    {
        $rules = config('ghuribd.availability');

        return [
            'start_date.required' => 'Please choose a start date.',
            'start_date.after_or_equal' => 'The start date cannot be in the past.',
            'start_date.before_or_equal' => "You can only publish availability up to {$rules['max_advance_days']} days ahead.",
            'end_date.required' => 'Please choose an end date.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'end_date.before_or_equal' => "You can only publish availability up to {$rules['max_advance_days']} days ahead.",
            'weekdays.required' => 'Select at least one day of the week.',
            'weekdays.min' => 'Select at least one day of the week.',
            'status.in' => 'Status must be either Available or Blocked.',
        ];
    }
}
