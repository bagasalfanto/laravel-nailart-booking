<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTreatmentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('treatment.update') === true;
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id;

        return [
            'name'        => ['required', 'string', 'max:80', Rule::unique('treatment_categories', 'name')->ignore($id)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama kategori sudah dipakai.',
            'name.required' => 'Nama kategori wajib diisi.',
        ];
    }
}
