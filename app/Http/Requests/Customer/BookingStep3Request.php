<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BookingStep3Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name'  => ['required', 'string', 'max:50'],
            'email'      => ['required', 'string', 'email:rfc', 'max:255'],
            'phone'      => ['required', 'string', 'regex:/^[0-9+\-\s]{8,20}$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->filled('first_name') ? trim((string) $this->input('first_name')) : null,
            'last_name'  => $this->filled('last_name')  ? trim((string) $this->input('last_name'))  : null,
            'email'      => $this->filled('email') ? Str::lower(trim((string) $this->input('email'))) : null,
            'phone'      => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Nama depan harus diisi.',
            'last_name.required'  => 'Nama belakang harus diisi.',
            'email.required'      => 'Email harus diisi.',
            'email.email'         => 'Format email tidak valid.',
            'phone.required'      => 'Nomor HP harus diisi.',
            'phone.regex'         => 'Nomor HP hanya boleh angka, +, -, atau spasi (8-20 karakter).',
        ];
    }
}
