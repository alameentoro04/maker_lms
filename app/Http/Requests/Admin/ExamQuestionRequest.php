<?php

namespace App\Http\Requests\Admin;

use App\Models\ExamQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamQuestionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(ExamQuestion::TYPES)],
            'question' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.text' => ['required', 'string', 'max:500'],
            'options.*.is_correct' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $options = $this->input('options', []);
            $correctCount = collect($options)->filter(fn ($o) => ! empty($o['is_correct']))->count();

            if ($correctCount !== 1) {
                $validator->errors()->add('options', 'Exactly one option must be marked correct.');
            }
        });
    }
}
