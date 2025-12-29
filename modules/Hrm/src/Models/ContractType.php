<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ContractType extends Model
{
    public $timestamps = false;

    protected $connection = 'maria-hrm';

    protected $table = 'contract_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'employer_id',
    ];

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
}
