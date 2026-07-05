<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentCategory extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    protected $table = 'treatment_categories';

    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'id'          => 'string',
            'name'        => 'string',
            'description' => 'string',
            'sort_order'  => 'integer',
            'is_active'   => 'boolean',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    public function setCachePrefix(): string
    {
        return 'treatment_category.cache';
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(TreatmentKatalog::class, 'category_id', 'id');
    }

    public function activeTreatments(): HasMany
    {
        return $this->treatments()->where('is_active', true)->orderBy('sort_order')->orderBy('nama_jasa');
    }
}
