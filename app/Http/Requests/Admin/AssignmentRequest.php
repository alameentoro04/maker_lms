<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'instructions' => ['required', 'string'],
            'due_at' => ['nullable', 'date'],
            'allowed_file_types' => ['nullable', 'array'],
            'allowed_file_types.*' => ['string', 'max:10'],
            'max_file_size_kb' => ['required', 'integer', 'min:100', 'max:51200'],
            'allow_resubmission' => ['boolean'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
