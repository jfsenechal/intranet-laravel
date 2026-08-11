<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Database\Factories\EmployeeFactory;
use AcMarche\Hrm\Enums\InternTypeEnum;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int|null $prerequisite_id
 * @property string|null $uid
 * @property string $last_name
 * @property string $first_name
 * @property string|null $job_title
 * @property \Carbon\CarbonImmutable|null $birth_date
 * @property string|null $private_email
 * @property string|null $private_phone
 * @property string|null $private_mobile
 * @property \Carbon\CarbonImmutable|null $hired_at
 * @property \Carbon\CarbonImmutable|null $left_at
 * @property \Carbon\CarbonImmutable|null $salary_seniority_date
 * @property \Carbon\CarbonImmutable|null $scale_seniority_date
 * @property \Carbon\CarbonImmutable|null $reminder_date
 * @property StatusEnum|null $status
 * @property string|null $notes
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property string|null $photo
 * @property string|null $address
 * @property int|null $postal_code
 * @property string|null $city
 * @property string|null $national_registry_number
 * @property int|null $pay_scale_id
 * @property \Carbon\CarbonImmutable|null $received_at
 * @property string|null $mail_reference
 * @property string|null $diploma_level
 * @property string|null $diploma_level_simplified
 * @property string|null $diploma_nature
 * @property string|null $candidate_file_name
 * @property \Carbon\CarbonImmutable|null $mail_sent_at
 * @property int|null $mail_count
 * @property string|null $priority
 * @property string $user_add
 * @property bool $is_archived
 * @property bool|null $show_birthday
 * @property int|null $candidate_service_id
 * @property string|null $local_unit
 * @property string|null $pay_scale_code
 * @property string|null $allowance
 * @property string|null $civility
 * @property string|null $updated_by
 * @property int|null $health_insurance_id
 * @property string|null $insurance_affiliation
 * @property InternTypeEnum|null $intern_type
 * @property string|null $username
 * @property string $uuid
 * @property string|null $professional_email
 * @property string|null $professional_mobile
 * @property string|null $professional_phone
 * @property string|null $professional_phone_extension
 * @property string|null $longitude
 * @property string|null $latitude
 * @property bool|null $show_photo
 * @property string|null $emergency_contact
 * @property string|null $note
 * @property bool $is_new_hire
 * @property \Carbon\CarbonImmutable|null $is_new_hire_updated_at
 * @property-read PayScale|null $payScale
 * @property-read HealthInsurance|null $healthInsurance
 * @property-read Prerequisite|null $prerequisite
 * @property-read Service|null $candidateService
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Contract> $contracts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Contract> $activeContracts
 * @property-read Profile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Absence> $absences
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Training> $trainings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Evaluation> $evaluations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Diploma> $diplomas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Internship> $internships
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Application> $applications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HrDocument> $documents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Valorization> $valorizations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Deadline> $deadlines
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SmsReminder> $smsMessages
 * @property-read string $full_name
 */
#[Connection('maria-hrm')]
#[Fillable([
    'uuid',
    'uid',
    'username',
    'civility',
    'last_name',
    'first_name',
    'job_title',
    'birth_date',
    'show_birthday',
    'private_email',
    'private_phone',
    'private_mobile',
    'address',
    'postal_code',
    'city',
    'national_registry_number',
    'hired_at',
    'left_at',
    'salary_seniority_date',
    'scale_seniority_date',
    'reminder_date',
    'status',
    'notes',
    'photo',
    'pay_scale_id',
    'pay_scale_code',
    'local_unit',
    'allowance',
    'health_insurance_id',
    'insurance_affiliation',
    'intern_type',
    'prerequisite_id',
    'is_archived',
    'received_at',
    'mail_reference',
    'diploma_level',
    'diploma_level_simplified',
    'diploma_nature',
    'candidate_file_name',
    'mail_sent_at',
    'mail_count',
    'priority',
    'candidate_service_id',
    'user_add',
    'updated_by',
    'is_new_hire',
    'is_new_hire_updated_at',
    'emergency_contact',
])]
#[Table(name: 'employees')]
#[UseFactory(EmployeeFactory::class)]
final class Employee extends Model
{
    use HasFactory;
    use HasUserAdd;

    /**
     * @deprecated The `job_title` column on employees is deprecated and should not be used.
     *             Functions are derived from active contracts via the `activeContracts` relation.
     */
    public const string DEPRECATED_JOB_TITLE = 'job_title';

    /**
     * @return BelongsTo<PayScale>
     */
    public function payScale(): BelongsTo
    {
        return $this->belongsTo(PayScale::class);
    }

    /**
     * @return BelongsTo<HealthInsurance>
     */
    public function healthInsurance(): BelongsTo
    {
        return $this->belongsTo(HealthInsurance::class);
    }

    /**
     * @return BelongsTo<Prerequisite>
     */
    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Prerequisite::class);
    }

    /**
     * @return BelongsTo<Service>
     */
    public function candidateService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'candidate_service_id');
    }

    /**
     * @return HasMany<Contract>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * @return HasMany<Contract>
     */
    public function activeContracts(): HasMany
    {
        return $this->hasMany(Contract::class)->active();
    }

    /**
     * @return HasOne<Profile>
     */
    public function profile(): HasOne
    {
        $instance = (new Profile)->setConnection('maria-agent');

        return $this->newHasOne(
            $instance->newQuery(),
            $this,
            $instance->getTable().'.employee_id',
            'id',
        );
    }

    /**
     * @return HasMany<Absence>
     */
    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    /**
     * @return HasMany<Training>
     */
    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    /**
     * @return HasMany<Evaluation>
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * @return HasMany<Diploma>
     */
    public function diplomas(): HasMany
    {
        return $this->hasMany(Diploma::class);
    }

    /**
     * @return HasMany<Internship>
     */
    public function internships(): HasMany
    {
        return $this->hasMany(Internship::class);
    }

    /**
     * @return HasMany<Application>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return HasMany<HrDocument>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(HrDocument::class);
    }

    /**
     * @return HasMany<Valorization>
     */
    public function valorizations(): HasMany
    {
        return $this->hasMany(Valorization::class);
    }

    /**
     * @return HasMany<Deadline>
     */
    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class);
    }

    /**
     * @return HasMany<SmsReminder>
     */
    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsReminder::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::creating(function (Employee $employee): void {
            if (empty($employee->uuid)) {
                $employee->uuid = (string) Str::uuid();
            }
        });

        self::saving(function (Employee $employee): void {
            if (
                $employee->isDirty('is_new_hire')
                && $employee->is_new_hire
                && ! $employee->isDirty('is_new_hire_updated_at')
            ) {
                $employee->is_new_hire_updated_at = now();
            }
        });
    }

    protected function getFullNameAttribute(): string
    {
        return $this->last_name.' '.$this->first_name;
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hired_at' => 'date',
            'left_at' => 'date',
            'salary_seniority_date' => 'date',
            'scale_seniority_date' => 'date',
            'reminder_date' => 'date',
            'candidate_received_at' => 'date',
            'candidate_mail_sent_at' => 'date',
            'show_birthday' => 'boolean',
            'is_archived' => 'boolean',
            'is_new_hire' => 'boolean',
            'is_new_hire_updated_at' => 'datetime',
            'status' => StatusEnum::class,
            'intern_type' => InternTypeEnum::class,
        ];
    }
}
