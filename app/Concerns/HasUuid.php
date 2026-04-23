<?php

namespace App\Concerns;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the data type of the primary key ID.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Generate a new UUID for the model.
     *
     * @param string|null $keyname
     *
     * @return string
     */
    public static function generateUuid(?string $keyname): string
    {
        $uuid = null;

        do {
            $uuid = Str::uuid();
        } while (static::where($keyname ?? 'id', $uuid)->exists());

        return $uuid;
    }

    /**
     * Boot the UUID trait for the model.
     *
     * @return void
     */
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (!$model->{$model->getKeyName()}) {
                $model->{$model->getKeyName()} = static::generateUuid($model->getKeyName());
            }
        });
    }
}
