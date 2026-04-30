<?php

declare(strict_types=1);

namespace AcMarche\Agent\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Connection('maria-agent')]
#[Fillable([
    'name',
    'description',
    'service_id',
])]
final class ExternalApplication extends Model
{
    /**
     * @return BelongsToMany<Profile>
     */
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_external_application');
    }
}
