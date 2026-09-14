<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controller checks enrollment/access before this matters
    }

    public function rules(): array
    {
        $assignment = $this->route('assignment');

        return [
            'text_response' => ['nullable', 'string', 'max:10000'],
            'external_link' => ['nullable', 'url', 'max:255'],
            'file' => ['nullable', 'file', 'max:'.($assignment?->max_file_size_kb ?? 10240)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('text_response') && ! $this->filled('external_link') && ! $this->hasFile('file')) {
                $validator->errors()->add('text_response', 'Submit a file, a link, or a text response.');
            }
        });
    }
}
