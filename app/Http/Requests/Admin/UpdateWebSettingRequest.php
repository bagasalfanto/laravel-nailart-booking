<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('web-setting.update') === true;
    }

    public function rules(): array
    {
        return [
            'settings'   => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
