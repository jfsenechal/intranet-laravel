<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Prerequisite extends Model
{
    public $timestamps = false;

    protected $connection = 'maria-hrm';

    protected $table = 'prerequisites';

    protected $fillable = [
        'title',
        'profession',
        'description',
        'user',
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
     * @return HasMany<Employee>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
