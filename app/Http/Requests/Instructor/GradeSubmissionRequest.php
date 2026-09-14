<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade' => ['required', 'integer', 'min:0', 'max:100'],
            'instructor_feedback' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
