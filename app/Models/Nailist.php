<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Nailist extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    protected $fillable = [
        'user_id',
        'title',
        'bio',
        'instagram',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'user_id' => 'string',
            'title' => 'string',
            'bio' => 'string',
            'instagram' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function setCachePrefix(): string
    {
        return 'nailist.cache';
    }

    /**
     * `withTrashed()` agar booking & portfolio history tetap bisa resolve nama
     * meski user-nya sudah soft-deleted.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }

    /**
     * Nailist yang user-nya masih aktif, belum soft-deleted, dan profilnya lengkap
     * (punya nama) — dipakai untuk semua daftar/selectable publik.
     *
     * `whereNull('deleted_at')` eksplisit karena relasi user() ber-withTrashed(),
     * jadi whereHas defaultnya menyertakan baris trashed. Filter `full_name`
     * menyaring akun nailist yang belum melengkapi profil (full_name kosong).
     */
    public function scopeActive($query)
    {
        return $query->whereHas('user', fn ($q) => $q
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->whereNotNull('full_name')
            ->where('full_name', '!=', ''));
    }

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

    public function reservations()
    {
        return $this->hasMany(Reservasi::class, 'nailist_id', 'id');
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'nailist_specialty')->withTimestamps();
    }

    /**
     * Hitung total client unik yang pernah booking dengan nailist ini.
     */
    public function getTotalClientsAttribute(): int
    {
        return (int) $this->reservations()->distinct('customer_id')->count('customer_id');
    }

    /**
     * Format URL Instagram — handle "@username", "username", atau full URL.
     */
    public function getInstagramUrlAttribute(): ?string
    {
        $ig = trim((string) $this->instagram);
        if ($ig === '') {
            return null;
        }

        if (str_starts_with($ig, 'http')) {
            return $ig;
        }

        $handle = ltrim($ig, '@');

        return 'https://instagram.com/'.$handle;
    }

    public function getInstagramHandleAttribute(): ?string
    {
        $ig = trim((string) $this->instagram);
        if ($ig === '') {
            return null;
        }

        if (preg_match('~instagram\.com/([^/?#]+)~i', $ig, $m)) {
            return '@'.$m[1];
        }

        return '@'.ltrim($ig, '@');
    }
}
