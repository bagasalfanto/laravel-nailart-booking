<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->user()->id;
        $isNailist = $this->user()->hasRole('nailist');

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($userId)],
            // Email read-only di profile — tidak boleh diubah dari sini.
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/', Rule::unique(User::class)->ignore($userId)],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],

            // Nailist-only fields (hanya divalidasi kalau user role nailist).
            'title' => [$isNailist ? 'nullable' : 'prohibited', 'string', 'max:255'],
            'bio' => [$isNailist ? 'nullable' : 'prohibited', 'string', 'max:1000'],
            'specialties' => [$isNailist ? 'array' : 'prohibited', 'max:3'],
            'specialties.*' => ['string', 'exists:specialties,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone_number')) {
            $this->merge([
                'phone_number' => preg_replace('/[^0-9]/', '', (string) $this->input('phone_number')),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'specialties.max' => 'Maksimal 3 specialty yang bisa dipilih.',
            'phone_number.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'phone_number.unique' => 'Nomor telepon sudah terdaftar.',
        ];
    }
}
