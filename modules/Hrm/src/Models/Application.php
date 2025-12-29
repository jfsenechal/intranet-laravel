<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Application extends Model
{
    protected $connection = 'maria-hrm';

    protected $table = 'applications';

    protected $fillable = [
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
    ];

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
