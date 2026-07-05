<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Eloquent\Model;

class WebSetting extends Model
{
    use HasUuid, Loggable, MakeCacheable;

    protected $table = 'web_settings';

    protected $fillable = [
        'key',
        'group',
        'label',
        'value',
        'type',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'key' => 'string',
            'group' => 'string',
            'label' => 'string',
            'value' => 'string',
            'type' => 'string',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function setCachePrefix(): string
    {
        return 'web_setting.cache';
    }
}
