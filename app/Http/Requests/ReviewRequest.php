<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    /**
     * Eligibility (own booking, Completed, not already reviewed) is enforced
     * by ReviewPolicy::create in the controller, not here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'review_text' => ['required', 'string', 'max:2000'],
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
            'rating.required' => 'Please select a star rating.',
            'rating.between' => 'Rating must be between 1 and 5 stars.',
            'review_text.required' => 'Please write a few words about your experience.',
            'review_text.max' => 'Your review may not be longer than 2000 characters.',
        ];
    }
}
