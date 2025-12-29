<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class HealthInsurance extends Model
{
    public $timestamps = false;

    protected $connection = 'maria-hrm';

    protected $table = 'health_insurances';

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Employee>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
