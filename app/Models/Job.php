<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Read-only view over the queue `jobs` table (pending / reserved jobs).
 *
 * @property int $id
 * @property string $queue
 * @property string $payload
 * @property int $attempts
 * @property int|null $reserved_at
 * @property int $available_at
 * @property int $created_at
 */
final class Job extends Model
{
    public $timestamps = false;

    protected $table = 'jobs';

    protected $guarded = [];

    /**
     * The queued job class name, decoded from the serialized payload.
     */
    public function displayName(): string
    {
        return data_get(json_decode((string) $this->payload, true), 'displayName', 'Unknown');
    }

    public function createdAt(): ?Carbon
    {
        return $this->created_at !== null ? Carbon::createFromTimestamp($this->created_at) : null;
    }

    public function availableAt(): ?Carbon
    {
        return $this->available_at !== null ? Carbon::createFromTimestamp($this->available_at) : null;
    }

    public function reservedAt(): ?Carbon
    {
        return $this->reserved_at !== null ? Carbon::createFromTimestamp($this->reserved_at) : null;
    }
}
