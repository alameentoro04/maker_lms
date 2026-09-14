<?php

namespace App\Http\Requests\Admin;

use App\Models\Cohort;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CohortRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cohortId = $this->route('cohort')?->id;

        return [
            'course_id' => ['required', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('cohorts', 'slug')->ignore($cohortId)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'enrollment_opens_at' => ['nullable', 'date'],
            'enrollment_closes_at' => ['nullable', 'date'],
            'capacity' => ['required', 'integer', 'min:1'],
            'live_platform' => ['required', 'string', 'max:50'],
            'learning_model' => ['required', Rule::in(['recorded', 'live', 'recorded_and_live'])],
            'status' => ['required', Rule::in(Cohort::STATUSES)],
            'instructor_ids' => ['nullable', 'array'],
            'instructor_ids.*' => ['exists:users,id'],
        ];
    }
}
