<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\AbsenceFactory;
use AcMarche\Hrm\Enums\ReasonsEnum;
use AcMarche\Hrm\Enums\WorkCapacityAssessmentEnum;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-hrm')]
#[Fillable([
    'employee_id',
    'start_date',
    'end_date',
    'reminder_date',
    'closed_date',
    'has_resumed',
    'notes',
    'ssa',
    'reason',
    'clock_updated',
    'encore',
    'is_closed',
    'acropole',
    'agent_file',
    'user_add',
    'updated_by',
    'work_capacity_assessment',
])]
#[Table(name: 'absences')]
#[UseFactory(AbsenceFactory::class)]
final class Absence extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reminder_date' => 'date',
            'closed_date' => 'date',
            'is_closed' => 'boolean',
            'certimed' => 'boolean',
            'has_resumed' => 'boolean',
            'clock_updated' => 'boolean',
            'acropole' => 'boolean',
            'agent_file' => 'boolean',
            'reason' => ReasonsEnum::class,
            'work_capacity_assessment' => WorkCapacityAssessmentEnum::class,
        ];
    }
}
