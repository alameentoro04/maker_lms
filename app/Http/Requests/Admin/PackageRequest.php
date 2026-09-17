<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $packageId = $this->route('package')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('packages', 'slug')->ignore($packageId)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'is_published' => ['boolean'],
            'cohort_ids' => ['required', 'array', 'min:2'],
            'cohort_ids.*' => ['exists:cohorts,id'],
        ];
    }
}
