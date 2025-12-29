<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Contract extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'contracts';

    protected $fillable = [
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
        ];
    }
}
