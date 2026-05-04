<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Connection('maria-aldermen-agenda')]
#[Fillable(['title', 'recipients', 'sent_at', 'content'])]
final class Archive extends Model
{
    use HasFactory;

    protected $table = 'agenda_echevin_archives';

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
