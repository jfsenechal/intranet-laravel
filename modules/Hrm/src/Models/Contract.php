<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Enums\ContractStatusEnum;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Connection('maria-hrm')]
#[Fillable([
    'employee_id',
    'employer_id',
    'direction_id',
    'service_id',
    'contract_nature_id',
    'contract_type_id',
    'pay_scale_id',
    'replaces_id',
    'college',
    'is_replacement',
    'start_date',
    'end_date',
    'reminder_date',
    'is_closed',
    'is_amendment',
    'is_suspended',
    'job_title',
    'status',
    'work_regime',
    'hourly_regime',
    'file1_name',
    'file2_name',
    'user_add',
    'updated_by',
])]
#[Table(name: 'contracts')]
final class Contract extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * @deprecated The `status` column is deprecated and should not be used.
     *             Activity is determined by `is_closed`, `is_suspended` and `end_date`.
     */
    public const string DEPRECATED_STATUS = 'status';

    #[Scope]
    public static function active(Builder $query): void
    {
        $query->where('is_closed', false)
            ->where('is_suspended', false)
            ->where(function (Builder $query): void {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            });
    }

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
     * @return BelongsTo<Direction>
     */
    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    /**
     * @return BelongsTo<Service>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<ContractNature>
     */
    public function contractNature(): BelongsTo
    {
        return $this->belongsTo(ContractNature::class);
    }

    /**
     * @return BelongsTo<ContractType>
     */
    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    /**
     * @return BelongsTo<PayScale>
     */
    public function payScale(): BelongsTo
    {
        return $this->belongsTo(PayScale::class);
    }

    /**
     * @return BelongsTo<Employee>
     */
    public function replaces(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'replaces_id');
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
            'is_closed' => 'boolean',
            'is_amendment' => 'boolean',
            'is_suspended' => 'boolean',
            'work_regime' => 'float',
            'status' => ContractStatusEnum::class,
        ];
    }
}
