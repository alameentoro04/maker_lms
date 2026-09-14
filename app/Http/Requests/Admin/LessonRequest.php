<?php

namespace App\Http\Requests\Admin;

use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(Lesson::TYPES)],
            'content' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }
}
