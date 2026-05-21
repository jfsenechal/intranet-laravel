<?php

declare(strict_types=1);

namespace AcMarche\College\Models;

use AcMarche\College\Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseFactory(NotificationFactory::class)]
#[Connection('maria-college')]
#[Fillable([
    'file_name',
    'mime',
    'updatedAt',
])]
final class Notification extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    public const UPDATED_AT = 'updatedAt';

    protected $table = 'college_notifications';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'updatedAt' => 'datetime',
        ];
    }
}
