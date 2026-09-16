<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LiveClassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'meet_url' => ['required', 'url', 'max:255'],
            'status' => ['required', Rule::in(['scheduled', 'live', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'recording_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
