<?php

namespace App\Http\Requests;

use App\Enums\ComplaintCategory;
use App\Enums\UserRole;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreComplaintRequest extends FormRequest
{
    /**
     * Only Travelers and Travel Partners file complaints.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && ($user->hasRole(UserRole::TRAVELER->value) || $user->hasRole(UserRole::TRAVEL_PARTNER->value));
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'in:'.implode(',', ComplaintCategory::values())],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * Cross-field check: a referenced booking must belong to the user
     * filing the complaint (as traveler, or as the resort/package owner).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $bookingId = $this->input('booking_id');

            if (! $bookingId) {
                return;
            }

            $booking = Booking::find($bookingId);
            $user = $this->user();

            if (! $booking) {
                return;
            }

            $ownsAsTraveler = $booking->user_id === $user->id;
            $ownsAsPartner = $booking->resort?->user_id === $user->id || $booking->tourPackage?->user_id === $user->id;

            if (! $ownsAsTraveler && ! $ownsAsPartner) {
                $validator->errors()->add('booking_id', 'You may only reference a booking you are part of.');
            }
        });
    }
}
