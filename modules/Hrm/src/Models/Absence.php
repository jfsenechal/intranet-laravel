<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Absence extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'absences';

    protected $fillable = [
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
    ];

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
        ];
    }
}
