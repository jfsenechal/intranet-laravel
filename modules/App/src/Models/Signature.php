<?php

declare(strict_types=1);

namespace AcMarche\App\Models;

use AcMarche\App\Enums\SignatureEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'signatures')]
#[Fillable([
    'last_name',
    'first_name',
    'address',
    'postal_code',
    'city',
    'service',
    'job_title',
    'email',
    'email_service',
    'username',
    'phone',
    'mobile',
    'website',
    'logo',
    'logo_title',
])]
final class Signature extends Model
{
    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    /**
     * Cast the logo by hand rather than with the enum cast: the column holds file
     * names, and a file name that has been renamed in SignatureEnum must degrade to
     * the commune logo instead of throwing on every read of the record.
     *
     * @return Attribute<SignatureEnum|null, string|null>
     */
    protected function logo(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?SignatureEnum => SignatureEnum::fromFileName($value),
            set: fn (SignatureEnum|string|null $value): ?string => ($value instanceof SignatureEnum
                ? $value
                : SignatureEnum::fromFileName($value))?->value,
        );
    }

    protected function casts(): array
    {
        return [
            'postal_code' => 'integer',
        ];
    }
}
