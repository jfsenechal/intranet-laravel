<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only view over the queue `failed_jobs` table.
 *
 * @property int $id
 * @property string $uuid
 * @property string $connection
 * @property string $queue
 * @property string $payload
 * @property string $exception
 * @property \Illuminate\Support\Carbon $failed_at
 */
final class FailedJob extends Model
{
    public $timestamps = false;

    protected $table = 'failed_jobs';

    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'failed_at' => 'datetime',
    ];

    /**
     * The queued job class name, decoded from the serialized payload.
     */
    public function displayName(): string
    {
        return data_get(json_decode((string) $this->payload, true), 'displayName', 'Unknown');
    }

    /**
     * The first line of the stored exception (its message and class).
     */
    public function exceptionSummary(): string
    {
        return mb_trim(strtok((string) $this->exception, "\n") ?: '');
    }
}
