<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class DataCharm extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_charm',
        'stok',
        'harga',
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
            'nama_charm' => 'string',
            'stok' => 'integer',
            'harga' => 'decimal:2',
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
        return 'data_charm.cache';
    }

    /**
     * Get the charm usages associated with this charm.
     */
    public function penggunaanCharms()
    {
        return $this->hasMany(PenggunaanCharm::class, 'charm_id', 'id');
    }
}