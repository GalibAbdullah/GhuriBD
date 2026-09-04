<?php

namespace App\Http\Requests;

use App\Enums\TourPackageService;
use App\Enums\TourPackageStatus;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TourPackageRequest extends FormRequest
{
    /**
     * Only authenticated, verified Travel Partners can create or update a tour
     * package. Ownership of an existing package is enforced separately via
     * TourPackagePolicy in the controller.
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
        $divisions = array_keys(config('bangladesh.divisions'));
        $isCreating = $this->isMethod('post');

        return [
            'title' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'division' => ['required', 'string', Rule::in($divisions)],
            'district' => ['required', 'string', $this->districtRule()],
            'description' => ['required', 'string', 'max:5000'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'duration_nights' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'max_travelers' => ['required', 'integer', 'min:1'],
            'meeting_point' => ['required', 'string', 'max:255'],
            'start_location' => ['required', 'string', 'max:255'],
            'itinerary' => ['required', 'string', 'max:20000'],
            'included_services' => ['nullable', 'array'],
            'included_services.*' => ['string', Rule::in(TourPackageService::values())],
            'excluded_services' => ['nullable', 'array'],
            'excluded_services.*' => ['string', Rule::in(TourPackageService::values())],
            'status' => ['required', 'string', Rule::in(TourPackageStatus::values())],
            'cover_image' => [
                $isCreating ? 'required' : 'sometimes',
                'file',
                'mimes:jpeg,jpg,png,gif,webp,bmp,svg,avif,heic,heif,tiff',
                'max:4096',
            ],
            'gallery_images' => ['nullable', 'array', 'max:10'],
            'gallery_images.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp,bmp,svg,avif,heic,heif,tiff', 'max:4096'],
            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => ['integer', 'exists:tour_package_images,id'],
        ];
    }

    /**
     * The district must belong to the selected division.
     */
    protected function districtRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $districts = config('bangladesh.divisions.'.$this->input('division'), []);

            if (! in_array($value, $districts, true)) {
                $fail('Please select a district that belongs to the chosen division.');
            }
        };
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a title for this tour package.',
            'destination.required' => 'Please enter the destination.',
            'division.required' => 'Please select a division.',
            'division.in' => 'Please select a valid division.',
            'district.required' => 'Please select a district.',
            'description.required' => 'A description helps travelers know what to expect.',
            'duration_days.required' => 'Please enter the number of days.',
            'duration_nights.required' => 'Please enter the number of nights.',
            'price.required' => 'Please set a price for this package.',
            'price.numeric' => 'The price must be a number.',
            'max_travelers.required' => 'Please enter the maximum number of travelers.',
            'meeting_point.required' => 'Please specify a meeting point.',
            'start_location.required' => 'Please specify a start location.',
            'itinerary.required' => 'Please provide the day-by-day itinerary.',
            'included_services.*.in' => 'One of the selected included services is invalid.',
            'excluded_services.*.in' => 'One of the selected excluded services is invalid.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Please select a valid status.',
            'cover_image.required' => 'Please upload a cover image for this package.',
            'cover_image.image' => 'The cover image must be a valid image file.',
            'cover_image.mimes' => 'The cover image must be a valid image file (JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF).',
            'cover_image.max' => 'The cover image may not be larger than 4 MB.',
            'gallery_images.max' => 'You may upload up to 10 gallery images at a time.',
            'gallery_images.*.image' => 'Each gallery upload must be a valid image file.',
            'gallery_images.*.mimes' => 'Gallery images must be a valid image file (JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF).',
            'gallery_images.*.max' => 'Each gallery image may not be larger than 4 MB.',
        ];
    }
}
