<?php

declare(strict_types=1);

namespace AcMarche\Agent\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-agent')]
#[Table(name: 'profile_phone')]
#[Fillable([
    'profile_id',
    'existing_number',
    'new_number',
    'external_number',
    'mobile_number',
])]
final class ProfilePhone extends Model
{
    /**
     * @return BelongsTo<Profile>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    protected function casts(): array
    {
        return [
            'new_number' => 'boolean',
            'external_number' => 'boolean',
        ];
    }
}
