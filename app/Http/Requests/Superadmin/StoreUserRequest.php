<?php

namespace App\Http\Requests\Superadmin;

use App\Models\User;
use App\Support\SystemSetting;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'superadmin']) === true;
    }

    public function rules(): array
    {
        $isSuperadmin = $this->user()?->hasRole('superadmin') === true;

        // Admin → hanya nailist; Superadmin → admin atau nailist.
        $allowedRoles = $isSuperadmin ? ['admin', 'nailist'] : ['nailist'];

        $domain = SystemSetting::emailDomain();
        $domainRegex = '/@'.preg_quote($domain, '/').'$/i';

        return [
            'role'  => ['required', 'string', 'in:'.implode(',', $allowedRoles)],
            'email' => [
                'required', 'string', 'email', 'lowercase', 'max:255',
                'regex:'.$domainRegex,
                'unique:'.User::class.',email',
            ],
        ];
    }

    public function messages(): array
    {
        $domain = SystemSetting::emailDomain();

        return [
            'role.required' => 'Role wajib dipilih.',
            'role.in'       => 'Role yang dipilih tidak diizinkan untuk akun ini.',
            'email.regex'   => "Email harus menggunakan domain @{$domain}.",
            'email.unique'  => 'Email sudah terdaftar.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }
}
