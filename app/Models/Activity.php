<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\Loggable;
use App\Concerns\MakeCacheable;
use Illuminate\Database\Connection;
use Spatie\Activitylog\Models\Activity as BaseActivity;

class Activity extends BaseActivity
{
    use HasUuid, Loggable, MakeCacheable;

    /**
     * Disable activity logging on the activity model itself.
     *
     * @var array<int, string>
     */
    protected static array $recordEvents = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_log';

    /**
     * Indicates if the IDs are incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'id' => 'string',
        ]);
    }

    /**
     * Set the cache prefix.
     */
    public function setCachePrefix(): string
    {
        return 'activity.cache';
    }

    /**
     * Forward concrete connection return type for cacheable trait compatibility.
     */
    public function getConnection(): Connection
    {
        return parent::getConnection();
    }
}
