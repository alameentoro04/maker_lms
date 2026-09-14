<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controller already authorizes via Policy before this runs
    }

    public function rules(): array
    {
        $courseId = $this->route('course')?->id;

        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('courses', 'slug')->ignore($courseId)],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'objectives' => ['nullable', 'array'],
            'objectives.*' => ['string', 'max:255'],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['string', 'max:255'],
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration_weeks' => ['required', 'integer', 'min:1', 'max:52'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'price' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'instructor_ids' => ['nullable', 'array'],
            'instructor_ids.*' => ['exists:users,id'],
        ];
    }
}
