<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

final class Employee extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'employees';

    protected $fillable = [
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
        'email',
        'professional_email',
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
        'candidate_received_at',
        'candidate_mail_reference',
        'candidate_diploma_level',
        'candidate_diploma_nature',
        'candidate_file_name',
        'candidate_mail_sent_at',
        'candidate_mail_count',
        'candidate_priority',
        'candidate_service_id',
        'saved_employer_id',
        'user_add',
        'updated_by',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->last_name.' '.$this->first_name;
    }

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
     * @return BelongsTo<Employer>
     */
    public function savedEmployer(): BelongsTo
    {
        return $this->belongsTo(Employer::class, 'saved_employer_id');
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
     * @return HasMany<Sms>
     */
    public function smsMessages(): HasMany
    {
        return $this->hasMany(Sms::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::creating(function (Employee $employee) {
            if (empty($employee->uuid)) {
                $employee->uuid = (string) Str::uuid();
            }
            if (empty($employee->slug)) {
                $employee->slug = Str::slug($employee->last_name.'-'.$employee->first_name);
            }
        });
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
        ];
    }
}
