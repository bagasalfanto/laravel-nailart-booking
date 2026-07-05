<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('specialty.create') === true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:80', Rule::unique('specialties', 'name')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
