<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class TreatmentKatalog extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_jasa',
        'kode_jasa',
        'deskripsi',
        'estimasi_harga',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'nama_jasa' => 'string',
            'kode_jasa' => 'string',
            'deskripsi' => 'string',
            'estimasi_harga' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Set the cache prefix.
     *
     * @return string
     */
    public function setCachePrefix(): string {
        return 'treatment_katalog.cache';
    }

    /**
     * Get the reservations that use this treatment.
     */
    public function reservations()
    {
        return $this->hasMany(Reservasi::class, 'treatment_id', 'id');
    }
}
