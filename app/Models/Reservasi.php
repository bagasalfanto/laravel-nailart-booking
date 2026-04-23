<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'nailist_id',
        'treatment_id',
        'status_id',
        'tanggal',
        'jam',
        'waktu_mulai',
        'waktu_selesai',
        'referensi_desain',
        'total_harga_final',
        'booking_notified_at',
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
            'customer_id' => 'string',
            'nailist_id' => 'string',
            'treatment_id' => 'string',
            'status_id' => 'string',
            'tanggal' => 'date',
            'jam' => 'string',
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'referensi_desain' => 'string',
            'total_harga_final' => 'decimal:2',
            'booking_notified_at' => 'datetime',
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
        return 'reservasi.cache';
    }

    /**
     * Get the customer that made the reservation.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Get the nailist assigned to the reservation.
     */
    public function nailist()
    {
        return $this->belongsTo(Nailist::class, 'nailist_id', 'id');
    }

    /**
     * Get the treatment chosen for the reservation.
     */
    public function treatment()
    {
        return $this->belongsTo(TreatmentKatalog::class, 'treatment_id', 'id');
    }

    /**
     * Get the status of the reservation.
     */
    public function status()
    {
        return $this->belongsTo(StatusBooking::class, 'status_id', 'id');
    }

    /**
     * Get the charm usages for this reservation.
     */
    public function penggunaanCharms()
    {
        return $this->hasMany(PenggunaanCharm::class, 'reservasi_id', 'id');
    }

    /**
     * Get the payment associated with this reservation.
     */
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'reservasi_id', 'id');
    }
}
