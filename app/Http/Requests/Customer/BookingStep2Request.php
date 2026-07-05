<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingStep2Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'treatments'         => ['required', 'array', 'min:1'],
            'treatments.*'       => ['string', 'uuid', Rule::exists('treatment_katalogs', 'id')->where('is_active', true)],
            'charms'             => ['nullable', 'array'],
            'charms.*.id'        => ['required_with:charms', 'string', 'uuid', Rule::exists('data_charms', 'id')],
            // qty boleh 0 (artinya tidak ambil charm tsb) — di-filter di controller.
            'charms.*.qty'       => ['required_with:charms', 'integer', 'min:0', 'max:20'],
            'referensi_desain'   => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'treatments.required' => 'Pilih minimal satu treatment.',
            'treatments.min'      => 'Pilih minimal satu treatment.',
            'treatments.*.exists' => 'Salah satu treatment yang dipilih tidak valid.',
            'charms.*.id.exists'  => 'Charm yang dipilih tidak ditemukan.',
            'charms.*.qty.min'    => 'Jumlah charm minimal 1.',
            'charms.*.qty.max'    => 'Jumlah charm maksimal 20.',
            'referensi_desain.image' => 'Referensi desain harus berupa gambar.',
            'referensi_desain.max'   => 'Ukuran referensi maksimal 5 MB.',
        ];
    }
}
