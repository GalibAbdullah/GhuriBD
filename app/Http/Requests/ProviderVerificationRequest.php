<?php

namespace App\Http\Requests;

use App\Enums\ProviderType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class ProviderVerificationRequest extends FormRequest
{
    /**
     * Only authenticated Travel Partners can submit verification.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole(UserRole::TRAVEL_PARTNER->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider_name' => ['required', 'string', 'max:255'],
            'provider_type' => ['required', 'string', 'in:'.implode(',', ProviderType::values())],
            'business_address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:30'],
            'verification_document' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,jpeg,png,jpg',
                'max:10240', // 10 MB
            ],
            'additional_information' => ['nullable', 'string', 'max:2000'],
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
            'provider_name.required' => 'The business or provider name is required.',
            'provider_type.required' => 'Please select a provider type.',
            'provider_type.in' => 'Provider type must be one of: Resort Owner, Tour Operator, Tour Guide.',
            'business_address.required' => 'The business address is required.',
            'phone.required' => 'A contact phone number is required.',
            'verification_document.required' => 'Please upload a verification document.',
            'verification_document.file' => 'The verification document must be a valid file.',
            'verification_document.mimes' => 'The document must be a PDF, DOC, DOCX, JPEG, or PNG file.',
            'verification_document.max' => 'The document may not be larger than 10 MB.',
        ];
    }
}