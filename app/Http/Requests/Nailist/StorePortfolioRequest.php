<?php

namespace App\Http\Requests\Nailist;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('nailist') === true
            && $this->user()?->nailist !== null;
    }

    public function rules(): array
    {
        return [
            'judul'     => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'gambar'    => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required'   => 'Judul portfolio harus diisi.',
            'judul.max'        => 'Judul maksimal 100 karakter.',
            'deskripsi.max'    => 'Deskripsi maksimal 1000 karakter.',
            'gambar.required'  => 'Gambar portfolio harus diunggah.',
            'gambar.image'     => 'File harus berupa gambar.',
            'gambar.mimes'     => 'Gambar harus berformat JPG, PNG, atau WebP.',
            'gambar.max'       => 'Ukuran gambar maksimal 2 MB.',
        ];
    }
}
