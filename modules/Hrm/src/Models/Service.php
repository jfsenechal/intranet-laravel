<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Service extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'services';

    protected $fillable = [
        'title',
        'slug',
        'abbreviation',
        'direction_id',
        'employer_id',
        'address',
        'postal_code',
        'city',
        'email',
        'phone',
        'gsm',
        'notes',
        'user_add',
    ];

    /**
     * @return BelongsTo<Direction>
     */
    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    /**
     * @return BelongsTo<Employer>
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * @return HasMany<Contract>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * @return HasMany<Operator>
     */
    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }
}
