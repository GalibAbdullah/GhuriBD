<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use Illuminate\Foundation\Http\FormRequest;

class VerificationReviewRequest extends FormRequest
{
    /**
     * Only authenticated Admins can review verification requests.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:'.implode(',', VerificationStatus::values())],
            'rejection_reason' => [
                'nullable',
                'string',
                'max:2000',
                'required_if:status,'.VerificationStatus::REJECTED->value,
            ],
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
            'status.required' => 'A review decision is required.',
            'status.in' => 'The review decision must be either Approved or Rejected.',
            'rejection_reason.required_if' => 'Please provide a reason for rejecting this request.',
        ];
    }
}