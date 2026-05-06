<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\EvaluationFactory;
use AcMarche\Hrm\Enums\EvaluationResultEnum;
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
])]
#[Table(name: 'evaluations')]
#[UseFactory(EvaluationFactory::class)]
final class Evaluation extends Model
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
            'result' => EvaluationResultEnum::class,
        ];
    }
}
