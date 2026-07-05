<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingStep1Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('customer') ?? false;
    }

    public function rules(): array
    {
        return [
            'nailist_id' => ['required', 'string', 'uuid', Rule::exists('nailists', 'id')],
            'tanggal'    => ['required', 'date', 'after_or_equal:today'],
            'jam'        => ['required', 'date_format:H:i', 'after_or_equal:08:00'],
        ];
    }

    public function messages(): array
    {
        return [
            'nailist_id.required' => 'Pilih nailist terlebih dahulu.',
            'nailist_id.exists'   => 'Nailist yang dipilih tidak ditemukan.',
            'tanggal.required'    => 'Tanggal booking harus diisi.',
            'tanggal.after_or_equal' => 'Tanggal booking tidak boleh kemarin.',
            'jam.required'           => 'Jam booking harus diisi.',
            'jam.date_format'        => 'Format jam harus HH:MM (contoh 14:30).',
            'jam.after_or_equal'     => 'Jam booking minimal pukul 08:00.',
        ];
    }
}
