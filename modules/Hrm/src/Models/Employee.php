<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Enums\InternTypeEnum;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Connection('maria-hrm')]
#[Fillable([
    'uuid',
    'uid',
    'username',
    'slug',
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
])]
#[Table(name: 'employees')]
final class Employee extends Model
{
    use HasFactory;
    use HasSlug;
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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['last_name', 'first_name'])
            ->saveSlugsTo('slug');
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::creating(function (Employee $employee): void {
            if (empty($employee->uuid)) {
                $employee->uuid = (string)Str::uuid();
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
            'status' => StatusEnum::class,
            'intern_type' => InternTypeEnum::class,
        ];
    }
}
