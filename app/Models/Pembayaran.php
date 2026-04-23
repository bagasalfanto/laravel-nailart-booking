<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'reservasi_id',
        'order_id',
        'payment_url',
        'payment_token',
        'gateway_transaction_id',
        'jenis_pembayaran',
        'bank',
        'raw_response',
        'nominal',
        'status_pembayaran',
        'batas_waktu_bayar',
        'waktu_pembayaran',
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
            'order_id' => 'string',
            'payment_url' => 'string',
            'payment_token' => 'string',
            'gateway_transaction_id' => 'string',
            'jenis_pembayaran' => 'string',
            'bank' => 'string',
            'raw_response' => 'array',
            'nominal' => 'decimal:2',
            'status_pembayaran' => 'string',
            'batas_waktu_bayar' => 'datetime',
            'waktu_pembayaran' => 'datetime',
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
        return 'pembayaran.cache';
    }

    /**
     * Get the reservation associated with the payment.
     */
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'id');
    }
}
