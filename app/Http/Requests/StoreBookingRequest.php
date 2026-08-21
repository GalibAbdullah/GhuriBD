<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\GuideAvailability;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('create', Booking::class);
    }

    public function rules(): array
    {
        $rules = config('ghuribd.booking');

        return [
            'availability_id' => ['required', 'integer', 'exists:guide_availabilities,id'],
            'party_size' => [
                'required',
                'integer',
                'min:'.$rules['min_party_size'],
                'max:'.$rules['max_party_size'],
            ],
        ];
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateSlotIsBookable($validator),
            fn (Validator $validator) => $this->validatePartySizeFitsCapacity($validator),
        ];
    }

    public function availability(): ?GuideAvailability
    {
        return GuideAvailability::find($this->input('availability_id'));
    }

    private function validateSlotIsBookable(Validator $validator): void
    {
        if ($validator->errors()->has('availability_id')) {
            return;
        }

        if (! $this->availability()->isBookable()) {
            $validator->errors()->add('availability_id', 'This slot is no longer available for booking.');
        }
    }

    /**
     * A fail-fast, informational check for a good error message. The
     * authoritative capacity check happens again — atomically, under a row
     * lock — when payment is confirmed, since capacity can shift between now
     * and then.
     */
    private function validatePartySizeFitsCapacity(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['availability_id', 'party_size'])) {
            return;
        }

        $availability = $this->availability();

        if ((int) $this->input('party_size') > $availability->remainingCapacity()) {
            $validator->errors()->add(
                'party_size',
                "Only {$availability->remainingCapacity()} spot(s) remain in this slot.",
            );
        }
    }

    public function messages(): array
    {
        $rules = config('ghuribd.booking');

        return [
            'availability_id.required' => 'Please choose a slot to book.',
            'availability_id.exists' => 'That slot could not be found.',
            'party_size.required' => 'Please say how many travelers this booking is for.',
            'party_size.min' => "A booking must be for at least {$rules['min_party_size']} traveler.",
            'party_size.max' => "A single booking cannot exceed {$rules['max_party_size']} travelers.",
        ];
    }
}
