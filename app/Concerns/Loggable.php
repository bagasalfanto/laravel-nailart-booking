<?php

namespace App\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

trait Loggable
{
    use LogsActivity;

    /**
     * The spatie log name.
     *
     * @var string
     */
    protected static string $logName = 'model';

    /**
     * The parse description for the spatie log.
     *
     * @param string $eventName
     *
     * @return string
     */
    private function parseDescription(string $eventName): string
    {
        $logName = ucfirst(static::$logName);
        $table = $this->table ? ucwords(str_replace(['-', '_'], ' ', $this->table)) : 'N/A';
        return "{$logName} {$table} has been {$eventName}";
    }

    /**
     * Set the loggable fields.
     *
     * @return array<string>
     */
    protected function setLoggableField(): array {
        return $this->fillable;
    }

    /**
     * The spatie log that setting log option.
     *
     * @var bool
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->setLoggableField())
            ->useLogName(static::$logName)
            ->setDescriptionForEvent(fn (string $eventName) => $this->parseDescription($eventName));
    }
}
