<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'full_name'    => ['required', 'string', 'max:255', 'regex:/^[A-Za-z .\-]+$/'],
            'email'        => [
                'required', 'string', 'lowercase', 'email', 'max:255', new SafeEmail(),
                Rule::unique(User::class, 'email')->whereNull('deleted_at'),
                Rule::unique(User::class, 'backup_email')
                    ->whereNotNull('backup_email_verified_at')
                    ->whereNull('deleted_at'),
            ],
            'phone_number' => [
                'nullable', 'string', 'max:20', 'regex:/^[0-9]+$/',
                Rule::unique(User::class, 'phone_number')->whereNull('deleted_at'),
            ],
            'password'     => ['required', 'string', 'max:72', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Bersihkan input sebelum validasi (trim + lowercase email + strip spasi phone).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name'    => $this->filled('full_name') ? trim((string) $this->input('full_name')) : null,
            'email'        => $this->filled('email')    ? Str::lower(trim((string) $this->input('email'))) : null,
            'phone_number' => $this->filled('phone_number')
                ? preg_replace('/[^0-9]/', '', (string) $this->input('phone_number'))
                : null,
        ]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required'   => 'Nama lengkap harus diisi',
            'full_name.regex'      => 'Nama hanya boleh berisi huruf, spasi, titik, dan strip.',
            'email.required'       => 'Email harus diisi',
            'email.unique'         => 'Email sudah terdaftar',
            'email.email'          => 'Email tidak valid',
            'phone_number.regex'   => 'Nomor telepon hanya boleh berisi angka.',
            'phone_number.unique'  => 'Nomor telepon sudah terdaftar',
            'password.required'    => 'Password harus diisi',
            'password.confirmed'   => 'Konfirmasi password tidak cocok.',
        ];
    }
}
