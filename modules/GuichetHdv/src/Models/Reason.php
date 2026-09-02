<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Models;

use AcMarche\GuichetHdv\Database\Factories\ReasonFactory;
use AcMarche\GuichetHdv\Enums\ServicesEnum;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Connection('maria-guichet')]
#[Fillable(['content', 'service'])]
final class Reason extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static function newFactory(): ReasonFactory
    {
        return ReasonFactory::new();
    }

    protected function casts(): array
    {
        return [
            'service' => ServicesEnum::class,
        ];
    }
}
