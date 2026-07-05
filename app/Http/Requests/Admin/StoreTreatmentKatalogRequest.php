<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTreatmentKatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('treatment.create') === true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'string', 'exists:treatment_categories,id'],
            'nama_jasa'   => ['required', 'string', 'max:120'],
            'kode_jasa'   => ['required', 'string', 'max:32', Rule::unique('treatment_katalogs', 'kode_jasa')],
            'deskripsi'   => ['nullable', 'string', 'max:500'],
            'price_type'  => ['required', 'in:fixed,range'],
            'price_min'   => ['required', 'numeric', 'min:0'],
            'price_max'    => ['nullable', 'numeric', 'min:0', 'gte:price_min', 'required_if:price_type,range'],
            'durasi_menit' => ['nullable', 'integer', 'min:15'],
            'is_active'    => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_jasa.unique'      => 'Kode treatment sudah dipakai.',
            'price_max.required_if' => 'Harga maksimum wajib diisi jika tipe harga "range".',
            'price_max.gte'         => 'Harga maksimum harus ≥ harga minimum.',
        ];
    }
}
