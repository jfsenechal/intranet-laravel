<?php

declare(strict_types=1);

namespace AcMarche\College\Models;

use AcMarche\College\Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property string $email
 * @property bool $pv_service
 * @property bool $ordre_service
 * @property bool $ordre_college
 * @property bool $pv_college
 */
#[UseFactory(RecipientFactory::class)]
#[Connection('maria-college')]
#[Fillable([
    'last_name',
    'first_name',
    'email',
    'pv_service',
    'ordre_service',
    'ordre_college',
    'pv_college',
])]
final class Recipient extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'college_recipients';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pv_service' => 'boolean',
            'ordre_service' => 'boolean',
            'ordre_college' => 'boolean',
            'pv_college' => 'boolean',
        ];
    }
}
