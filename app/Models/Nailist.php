<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class Nailist extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'specialty',
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
            'specialty' => 'string',
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
        return 'nailist.cache';
    }

    /**
     * Get the user that owns the nailist profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the portfolios for the nailist.
     */
    public function portfolios()
    {
        return $this->hasMany(Portfolio::class, 'nailist_id', 'id');
    }

    /**
     * Backward-compatible alias for historical misspelling.
     */
    public function portofolios()
    {
        return $this->portfolios();
    }

    /**
     * Get the reservations for the nailist.
     */
    public function reservations()
    {
        return $this->hasMany(Reservasi::class, 'nailist_id', 'id');
    }
}
