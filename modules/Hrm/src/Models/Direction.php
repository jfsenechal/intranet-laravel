<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Direction extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'directions';

    protected $fillable = [
        'title',
        'slug',
        'director',
        'abbreviation',
        'employer_id',
        'user_add',
    ];

    /**
     * @return BelongsTo<Employer>
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * @return HasMany<Service>
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * @return HasMany<Contract>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }
}
