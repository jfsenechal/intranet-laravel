<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\HealthInsuranceFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Connection('maria-hrm')]
#[Fillable([
    'name',
])]
#[Table(name: 'health_insurances')]
#[UseFactory(HealthInsuranceFactory::class)]
final class HealthInsurance extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    /**
     * @return HasMany<Employee>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
