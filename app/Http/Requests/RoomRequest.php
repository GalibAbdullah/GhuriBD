<?php

namespace App\Http\Requests;

use App\Enums\RoomAmenity;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    /**
     * Only authenticated, verified Travel Partners can create or update a room.
     * Ownership of the parent resort is enforced separately via RoomPolicy
     * in the controller.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->hasRole(UserRole::TRAVEL_PARTNER->value)
            && $user->isVerifiedProvider();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isCreating = $this->isMethod('post');

        return [
            'room_name' => ['required', 'string', 'max:255'],
            'room_type' => ['required', 'string', Rule::in(RoomType::values())],
            'description' => ['required', 'string', 'max:5000'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'total_rooms' => ['required', 'integer', 'min:1'],
            'available_rooms' => ['required', 'integer', 'min:0', 'lte:total_rooms'],
            'bed_type' => ['required', 'string', 'max:100'],
            'room_size' => ['required', 'string', 'max:100'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', Rule::in(RoomAmenity::values())],
            'status' => ['required', 'string', Rule::in(RoomStatus::values())],
            'cover_image' => [
                $isCreating ? 'required' : 'sometimes',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:4096',
            ],
            'gallery_images' => ['nullable', 'array', 'max:10'],
            'gallery_images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => ['integer', 'exists:room_images,id'],
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
            'room_name.required' => 'Please enter a name for this room.',
            'room_type.required' => 'Please select a room type.',
            'room_type.in' => 'Please select a valid room type.',
            'description.required' => 'A description helps travelers know what to expect.',
            'price_per_night.required' => 'Please set a price per night.',
            'price_per_night.numeric' => 'The price per night must be a number.',
            'capacity.required' => 'Please enter how many guests this room fits.',
            'total_rooms.required' => 'Please enter the total number of rooms of this type.',
            'available_rooms.required' => 'Please enter how many rooms are currently available.',
            'available_rooms.lte' => 'Available rooms cannot exceed the total number of rooms.',
            'bed_type.required' => 'Please specify the bed type.',
            'room_size.required' => 'Please specify the room size.',
            'amenities.*.in' => 'One of the selected amenities is invalid.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Please select a valid status.',
            'cover_image.required' => 'Please upload a cover image for this room.',
            'cover_image.image' => 'The cover image must be a valid image file.',
            'cover_image.mimes' => 'The cover image must be a JPEG, PNG, or WEBP file.',
            'cover_image.max' => 'The cover image may not be larger than 4 MB.',
            'gallery_images.max' => 'You may upload up to 10 gallery images at a time.',
            'gallery_images.*.image' => 'Each gallery upload must be a valid image file.',
            'gallery_images.*.mimes' => 'Gallery images must be JPEG, PNG, or WEBP files.',
            'gallery_images.*.max' => 'Each gallery image may not be larger than 4 MB.',
        ];
    }
}
