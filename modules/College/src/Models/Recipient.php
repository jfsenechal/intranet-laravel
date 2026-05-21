<?php

declare(strict_types=1);

namespace AcMarche\College\Models;

use AcMarche\College\Database\Factories\DestinataireFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $slugname
 * @property string $last_name
 * @property string $first_name
 * @property string $email
 * @property bool $pv_service
 * @property bool $ordre_service
 * @property bool $ordre_college
 * @property bool $pv_college
 */
#[UseFactory(DestinataireFactory::class)]
#[Connection('maria-college')]
#[Fillable([
    'slugname',
    'nom',
    'prenom',
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

    protected static function booted(): void
    {
        $assignSlug = function (self $destinataire): void {
            if (! empty($destinataire->slugname)) {
                return;
            }
            $base = mb_trim((string) $destinataire->nom).'_'.mb_trim((string) $destinataire->prenom);
            $destinataire->slugname = Str::slug($base, '_');
        };

        self::creating($assignSlug);
        self::updating($assignSlug);
    }

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
