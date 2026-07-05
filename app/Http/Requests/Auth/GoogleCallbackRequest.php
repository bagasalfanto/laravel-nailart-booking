<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class GoogleCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validasi parameter callback dari Google OAuth.
     * Socialite sudah verifikasi `state` (CSRF) secara internal,
     * di sini kita pastikan `code` ada dan tidak ada error.
     */
    public function rules(): array
    {
        return [
            'code'              => ['required_without:error', 'string'],
            'state'             => ['nullable', 'string'],
            'scope'             => ['nullable', 'string'],
            'authuser'          => ['nullable', 'string'],
            'prompt'            => ['nullable', 'string'],
            'error'             => ['nullable', 'string'],
            'error_description' => ['nullable', 'string'],
        ];
    }

    public function hasError(): bool
    {
        return $this->filled('error');
    }
}
