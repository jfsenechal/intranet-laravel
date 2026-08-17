<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\ContractFactory;
use AcMarche\Hrm\Enums\ContractStatusEnum;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $replaces_id
 * @property int $employee_id
 * @property string|null $college
 * @property bool $is_replacement
 * @property \Carbon\CarbonImmutable|null $start_date
 * @property \Carbon\CarbonImmutable|null $end_date
 * @property bool|null $is_closed
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property \Carbon\CarbonImmutable|null $reminder_date
 * @property int|null $contract_nature_id
 * @property int|null $contract_type_id
 * @property int $employer_id
 * @property int|null $pay_scale_id
 * @property float|null $work_regime
 * @property string|null $job_title
 * @property string $user_add
 * @property int|null $direction_id
 * @property int|null $service_id
 * @property string|null $hourly_regime
 * @property bool|null $is_amendment
 * @property string|null $updated_by
 * @property string|null $file1_name
 * @property string|null $file2_name
 * @property \Carbon\CarbonImmutable|null $createdAt
 * @property \Carbon\CarbonImmutable|null $updatedAt
 * @property ContractStatusEnum|null $status
 * @property bool|null $is_suspended
 * @property-read Employee|null $employee
 * @property-read Employer|null $employer
 * @property-read Direction|null $direction
 * @property-read Service|null $service
 * @property-read ContractNature|null $contractNature
 * @property-read ContractType|null $contractType
 * @property-read PayScale|null $payScale
 * @property-read Employee|null $replaces
 */
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
#[UseFactory(ContractFactory::class)]
final class Contract extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * @deprecated The `status` column is deprecated and should not be used.
     *             Activity is determined by `is_closed`, `is_suspended` and `end_date`.
     */
    public const string DEPRECATED_STATUS = 'status';

    /**
     * A contract is active once it has started and as long as it is neither closed,
     * suspended nor expired. A missing date is treated as open ended: no start date
     * means the contract already runs, no end date means it never expires.
     *
     * The start date is compared to the end of today because the `date` cast writes
     * a midnight time component, which would otherwise push a contract starting
     * today out of the scope.
     */
    #[Scope]
    public static function active(Builder $query): void
    {
        $query->where('is_closed', false)
            ->where('is_suspended', false)
            ->where(function (Builder $query): void {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', Carbon::today()->endOfDay());
            })
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
            'is_replacement' => 'boolean',
            'is_suspended' => 'boolean',
            'work_regime' => 'float',
            'status' => ContractStatusEnum::class,
        ];
    }
}
