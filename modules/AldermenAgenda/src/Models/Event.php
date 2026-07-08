<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Models;

use AcMarche\AldermenAgenda\Enums\EventTypesEnum;
use AcMarche\AldermenAgenda\Enums\OrganizersEnum;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Connection('maria-aldermen-agenda')]
#[Fillable([
    'event_type',
    'name',
    'description',
    'start_at',
    'end_at',
    'reminder_at',
    'is_walk',
    'organizer',
    'location',
    'representative',
    'sent',
    'file1_name',
    'file2_name',
])]
#[Table(name: 'events')]
final class Event extends Model
{
    use HasFactory;

    public function __toString(): string
    {
        return $this->name;
    }

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'reminder_at' => 'datetime',
            'is_walk' => 'boolean',
            'sent' => 'boolean',
            'event_type' => EventTypesEnum::class,
            'organizer' => OrganizersEnum::class,
        ];
    }
}
