<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('specialty.update') === true;
    }

    public function rules(): array
    {
        $id = $this->route('specialty')?->id;

        return [
            'name'      => ['required', 'string', 'max:80', Rule::unique('specialties', 'name')->ignore($id)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
