<?php

namespace App\Http\Requests;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Room;
use App\Models\TourPackage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    /**
     * Only authenticated Travelers may create bookings.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole(UserRole::TRAVELER->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_type' => ['required', 'string', Rule::in(BookingType::values())],
            'resort_id' => ['required_if:booking_type,resort,combined', 'nullable', 'integer', 'exists:resorts,id'],
            'room_id' => ['required_if:booking_type,resort,combined', 'nullable', 'integer', 'exists:rooms,id'],
            'tour_package_id' => ['required_if:booking_type,package,combined', 'nullable', 'integer', 'exists:tour_packages,id'],
            'check_in_date' => ['required_if:booking_type,resort,combined', 'nullable', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required_if:booking_type,resort,combined', 'nullable', 'date', 'after:check_in_date'],
            'travel_date' => ['required_if:booking_type,package,combined', 'nullable', 'date', 'after_or_equal:today'],
            'guests' => ['required', 'integer', 'min:1', 'max:100'],
            'special_request' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'resort_id.required_if' => 'Please select a resort.',
            'room_id.required_if' => 'Please select a room.',
            'tour_package_id.required_if' => 'Please select a tour package.',
            'check_in_date.required_if' => 'Please choose a check-in date.',
            'check_out_date.required_if' => 'Please choose a check-out date.',
            'check_out_date.after' => 'Check-out date must be after the check-in date.',
            'travel_date.required_if' => 'Please choose a travel date.',
            'guests.required' => 'Please enter the number of guests.',
            'guests.min' => 'At least one guest is required.',
        ];
    }

    /**
     * Cross-field checks that go beyond simple rule syntax: the room really
     * belongs to the chosen resort, both listings are still active, the
     * party size fits, and no conflicting booking already exists.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('booking_type');
            $guests = (int) $this->input('guests');

            if (in_array($type, [BookingType::RESORT->value, BookingType::COMBINED->value], true)) {
                $this->validateRoomSelection($validator, $guests);
            }

            if (in_array($type, [BookingType::PACKAGE->value, BookingType::COMBINED->value], true)) {
                $this->validatePackageSelection($validator, $guests);
            }
        });
    }

    private function validateRoomSelection(Validator $validator, int $guests): void
    {
        $room = Room::find($this->input('room_id'));

        if (! $room) {
            return;
        }

        if ((string) $room->resort_id !== (string) $this->input('resort_id')) {
            $validator->errors()->add('room_id', 'The selected room does not belong to the selected resort.');

            return;
        }

        if (! $room->isAvailable()) {
            $validator->errors()->add('room_id', 'This room is not currently available for booking.');
        }

        if ($room->resort && ! $room->resort->isActive()) {
            $validator->errors()->add('resort_id', 'This resort is not currently accepting bookings.');
        }

        if ($guests > $room->capacity) {
            $validator->errors()->add('guests', "This room fits at most {$room->capacity} guests.");
        }

        $checkIn = $this->input('check_in_date');
        $checkOut = $this->input('check_out_date');

        if ($checkIn && $checkOut) {
            $overlapping = Booking::query()
                ->overlappingForRoom($room->id, $checkIn, $checkOut)
                ->count();

            if ($overlapping >= $room->available_rooms) {
                $validator->errors()->add('check_in_date', 'This room is fully booked for the selected dates. Please choose different dates.');
            }

            $duplicate = Booking::query()
                ->where('user_id', $this->user()->id)
                ->where('room_id', $room->id)
                ->whereIn('booking_status', [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value])
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('check_in_date', 'You already have an active booking for this room on overlapping dates.');
            }
        }
    }

    private function validatePackageSelection(Validator $validator, int $guests): void
    {
        $package = TourPackage::find($this->input('tour_package_id'));

        if (! $package) {
            return;
        }

        if (! $package->isActive()) {
            $validator->errors()->add('tour_package_id', 'This tour package is not currently accepting bookings.');
        }

        if ($guests > $package->max_travelers) {
            $validator->errors()->add('guests', "This package fits at most {$package->max_travelers} travelers.");
        }

        $travelDate = $this->input('travel_date');

        if ($travelDate) {
            $duplicate = Booking::query()
                ->where('user_id', $this->user()->id)
                ->where('tour_package_id', $package->id)
                ->where('travel_date', $travelDate)
                ->whereIn('booking_status', [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value])
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('travel_date', 'You already have an active booking for this package on that date.');
            }
        }
    }
}
