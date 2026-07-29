<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\ApplicationFactory;
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
 * @property int|null $employer_id
 * @property \Carbon\CarbonImmutable $received_at
 * @property string|null $mail_reference
 * @property string|null $public_call
 * @property string|null $notes
 * @property string|null $file
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property string|null $updated_by
 * @property int|null $job_function_id
 * @property bool|null $is_spontaneous
 * @property bool|null $is_public_call
 * @property bool|null $is_priority
 * @property-read Employee|null $employee
 * @property-read Employer|null $employer
 * @property-read JobFunction|null $jobFunction
 */
#[Connection('maria-hrm')]
#[Fillable([
    'employee_id',
    'employer_id',
    'job_function_id',
    'received_at',
    'mail_reference',
    'public_call',
    'notes',
    'file',
    'is_spontaneous',
    'is_public_call',
    'is_priority',
    'updated_by',
])]
#[Table(name: 'applications')]
#[UseFactory(ApplicationFactory::class)]
final class Application extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<Employer>
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    /**
     * @return BelongsTo<JobFunction>
     */
    public function jobFunction(): BelongsTo
    {
        return $this->belongsTo(JobFunction::class);
    }

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
            'is_spontaneous' => 'boolean',
            'is_public_call' => 'boolean',
            'is_priority' => 'boolean',
        ];
    }
}
