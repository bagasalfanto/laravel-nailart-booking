<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nailist_id',
        'gambar_url',
        'deskripsi',
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
            'nailist_id' => 'string',
            'gambar_url' => 'string',
            'deskripsi' => 'string',
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
        return 'portfolio.cache';
    }

    /**
     * Get the nailist that owns the portfolio.
     */
    public function nailist()
    {
        return $this->belongsTo(Nailist::class, 'nailist_id', 'id');
    }
}