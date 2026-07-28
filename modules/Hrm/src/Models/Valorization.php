<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\ValorizationFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $employee_id
 * @property string $employer_name
 * @property string $duration
 * @property string|null $regime
 * @property string|null $content
 * @property string|null $file_name
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $updated_by
 * @property-read Employee|null $employee
 */
#[Connection('maria-hrm')]
#[Fillable([
    'employee_id',
    'employer_name',
    'duration',
    'regime',
    'content',
    'file_name',
    'updated_by',
])]
#[Table(name: 'valorizations')]
#[UseFactory(ValorizationFactory::class)]
final class Valorization extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
