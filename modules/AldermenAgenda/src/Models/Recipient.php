<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Connection('maria-aldermen-agenda')]
#[Fillable(['last_name', 'first_name', 'email', 'ics'])]
#[Table(name: 'aldermen_recipients')]
final class Recipient extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function __toString(): string
    {
        return $this->last_name.' '.$this->first_name;
    }

    protected function casts(): array
    {
        return [
            'ics' => 'boolean',
        ];
    }
}
