<?php

namespace App\Http\Requests;

use App\Enums\ResortAmenity;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResortRequest extends FormRequest
{
    /**
     * Only authenticated, verified Travel Partners can create or update a resort.
     * Ownership of an existing resort is enforced separately via ResortPolicy
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
        $divisions = array_keys(config('bangladesh.divisions'));
        $isCreating = $this->isMethod('post');

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'division' => ['required', 'string', Rule::in($divisions)],
            'district' => ['required', 'string', $this->districtRule()],
            'address' => ['required', 'string', 'max:1000'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'price_range' => ['required', 'string', 'max:100'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', Rule::in(ResortAmenity::values())],
            'cover_image' => [
                $isCreating ? 'required' : 'sometimes',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:4096',
            ],
            'gallery_images' => ['nullable', 'array', 'max:10'],
            'gallery_images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => ['integer', 'exists:resort_images,id'],
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
            'name.required' => 'Please enter your resort\'s name.',
            'description.required' => 'A description helps travelers know what to expect.',
            'division.required' => 'Please select a division.',
            'division.in' => 'Please select a valid division.',
            'district.required' => 'Please select a district.',
            'address.required' => 'The full address is required.',
            'contact_phone.required' => 'A contact phone number is required.',
            'price_range.required' => 'Please provide a price range, e.g. ৳3,000 - ৳8,000.',
            'amenities.*.in' => 'One of the selected amenities is invalid.',
            'cover_image.required' => 'Please upload a cover image for your resort.',
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
