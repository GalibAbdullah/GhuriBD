<?php

namespace App\Http\Requests;

use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class ComplaintResponseRequest extends FormRequest
{
    /**
     * Only Admins respond to complaints.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole(UserRole::ADMIN->value);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:'.implode(',', ComplaintStatus::values())],
            'admin_response' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'admin_response.required' => 'Please write a response before updating the status.',
        ];
    }
}
