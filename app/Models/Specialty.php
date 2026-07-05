<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Specialty extends Model
{
    use HasUuid, Loggable;

    protected $fillable = ['name', 'slug', 'is_active'];

    protected function casts(): array
    {
        return [
            'id'        => 'string',
            'name'      => 'string',
            'slug'      => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Specialty $specialty) {
            if (empty($specialty->slug) && ! empty($specialty->name)) {
                $specialty->slug = static::uniqueSlug($specialty->name, $specialty->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base ?: Str::random(8);
        $i = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }

    public function nailists(): BelongsToMany
    {
        return $this->belongsToMany(Nailist::class, 'nailist_specialty')->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
