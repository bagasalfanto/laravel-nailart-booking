<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
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
            'user_id' => 'string',
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
        return 'customer.cache';
    }

    /**
     * Get the user that owns the customer profile.
     * `withTrashed()` agar review & booking history tetap bisa resolve nama
     * meski user-nya sudah soft-deleted.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }

    /**
     * Get the reservations for the customer.
     */
    public function reservations()
    {
        return $this->hasMany(Reservasi::class, 'customer_id', 'id');
    }

    /**
     * Get the reviews written by the customer.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id', 'id');
    }
}