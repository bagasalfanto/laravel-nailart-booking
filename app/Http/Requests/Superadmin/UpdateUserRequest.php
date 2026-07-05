<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') === true;
    }

    public function rules(): array
    {
        return [
            'roles'   => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
