<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Evaluation extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'evaluations';

    protected $fillable = [
        'employee_id',
        'direction_id',
        'evaluation_date',
        'next_evaluation_date',
        'validation_date',
        'notes',
        'result',
        'file1_name',
        'file2_name',
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

    /**
     * @return BelongsTo<Direction>
     */
    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
            'next_evaluation_date' => 'date',
            'validation_date' => 'date',
        ];
    }
}
