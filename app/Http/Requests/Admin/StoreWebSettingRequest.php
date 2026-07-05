<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Dashboard\Admin\WebSettingController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('web-setting.create') === true;
    }

    public function rules(): array
    {
        return [
            'key' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z0-9_\-]+$/i',
                Rule::unique('web_settings', 'key'),
            ],
            'group' => ['required', 'string', Rule::in(array_keys(WebSettingController::GROUPS))],
            'label' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'string', Rule::in(['text', 'textarea', 'url', 'image'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'value' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.regex' => 'Key hanya boleh huruf, angka, underscore, dan strip.',
        ];
    }
}
