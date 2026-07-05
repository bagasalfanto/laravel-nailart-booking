<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Review extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    protected $fillable = [
        'reservasi_id',
        'customer_id',
        'rating',
        'content',
        'is_featured',
        'featured_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'reservasi_id' => 'string',
            'customer_id' => 'string',
            'rating' => 'integer',
            'content' => 'string',
            'is_featured' => 'boolean',
            'featured_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function setCachePrefix(): string
    {
        return 'review.cache';
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Akses langsung ke User lewat Customer.
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Customer::class,
            'id',         // customers.id
            'id',         // users.id
            'customer_id',// reviews.customer_id
            'user_id',    // customers.user_id
        );
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
