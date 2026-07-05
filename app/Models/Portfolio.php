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
        'judul',
        'gambar_url',
        'deskripsi',
        'is_featured',
        'featured_at',
    ];

    protected $appends = ['gambar_full_url'];

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
            'judul' => 'string',
            'gambar_url' => 'string',
            'deskripsi' => 'string',
            'is_featured' => 'boolean',
            'featured_at' => 'datetime',
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * URL gambar siap pakai di <img src>:
     * - URL absolut → langsung
     * - Path absolut /storage/... → langsung
     * - Path relatif → prefix asset('storage/')
     */
    public function getGambarFullUrlAttribute(): string
    {
        $g = (string) $this->gambar_url;

        if ($g === '') return '';

        if (str_starts_with($g, 'http://') || str_starts_with($g, 'https://')) {
            return $g;
        }

        if (str_starts_with($g, '/storage/') || str_starts_with($g, '/')) {
            return url($g);
        }

        return asset('storage/'.$g);
    }
}