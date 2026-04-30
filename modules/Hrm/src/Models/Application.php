<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
