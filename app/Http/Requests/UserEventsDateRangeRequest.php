<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserEventsDateRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date', 'required_with:end_date'],
            'end_date' => ['nullable', 'date', 'required_with:start_date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.required_with' => 'Start date is required when end date is selected.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.required_with' => 'End date is required when start date is selected.',
            'end_date.after_or_equal' => 'End date must be the same as or after the start date.',
        ];
    }
}
