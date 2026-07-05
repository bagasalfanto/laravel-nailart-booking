<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentKatalog extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    protected $fillable = [
        'category_id',
        'nama_jasa',
        'kode_jasa',
        'deskripsi',
        'price_type',
        'price_min',
        'price_max',
        'sort_order',
        'durasi_menit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'id'          => 'string',
            'category_id' => 'string',
            'nama_jasa'   => 'string',
            'kode_jasa'   => 'string',
            'deskripsi'   => 'string',
            'price_type'  => 'string',
            'price_min'   => 'decimal:2',
            'price_max'   => 'decimal:2',
            'sort_order'   => 'integer',
            'durasi_menit' => 'integer',
            'is_active'    => 'boolean',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    public function setCachePrefix(): string
    {
        return 'treatment_katalog.cache';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TreatmentCategory::class, 'category_id', 'id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservasi::class, 'treatment_id', 'id');
    }

    /**
     * Format harga untuk ditampilkan: "Rp 150.000" (fixed) atau "Rp 150.000 – Rp 300.000" (range).
     */
    public function getPriceLabelAttribute(): string
    {
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');

        if ($this->price_type === 'range' && $this->price_max !== null) {
            return $rupiah($this->price_min).' – '.$rupiah($this->price_max);
        }

        return $rupiah($this->price_min);
    }
}
