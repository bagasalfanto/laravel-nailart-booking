<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class PenggunaanCharm extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reservasi_id',
        'charm_id',
        'jumlah_dipakai',
        'subtotal',
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
            'reservasi_id' => 'string',
            'charm_id' => 'string',
            'jumlah_dipakai' => 'integer',
            'subtotal' => 'decimal:2',
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
        return 'penggunaan_charm.cache';
    }

    /**
     * Get the reservation that owns the charm usage.
     */
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'id');
    }

    /**
     * Get the charm that is used.
     */
    public function charm()
    {
        return $this->belongsTo(DataCharm::class, 'charm_id', 'id');
    }
}