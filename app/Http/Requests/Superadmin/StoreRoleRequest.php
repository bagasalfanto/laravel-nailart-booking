<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') === true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions'   => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
