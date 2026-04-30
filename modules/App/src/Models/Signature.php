<?php

declare(strict_types=1);

namespace AcMarche\App\Models;

use App\Enums\SignatureEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
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

    protected function casts(): array
    {
        return [
            'logo' => SignatureEnum::class,
            'postal_code' => 'integer',
        ];
    }
}
