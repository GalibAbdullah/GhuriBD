<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only the authenticated user can update their own profile.
        // The controller resolves the user from the authenticated session,
        // so no cross-user updates can occur.
        return (bool) $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:Male,Female,Other'],
            'address' => ['nullable', 'string', 'max:1000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,bmp,svg,avif,heic,heif,tiff', 'max:5120'],
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
            'date_of_birth.before' => 'Date of birth must be a date in the past.',
            'gender.in' => 'Gender must be one of: Male, Female, Other.',
            'profile_photo.image' => 'Profile photo must be an image file.',
            'profile_photo.mimes' => 'Profile photo must be a valid image file (JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF).',
            'profile_photo.max' => 'Profile photo may not be larger than 5 MB.',
        ];
    }
}