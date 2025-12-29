<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Operator extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'operators';

    protected $fillable = [
        'service_id',
        'employer_id',
        'name',
        'slug',
        'contact_last_name',
        'contact_first_name',
        'job_title',
        'email',
        'phone',
        'gsm',
        'address',
        'postal_code',
        'city',
        'objectives',
        'notes',
        'transmitted_at',
        'received_at',
        'mail_reference',
        'mail_count',
        'diploma_level',
        'diploma_nature',
        'is_archived',
        'user_add',
    ];

    /**
     * @return BelongsTo<Service>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<Employer>
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected function casts(): array
    {
        return [
            'transmitted_at' => 'date',
            'received_at' => 'date',
            'is_archived' => 'boolean',
        ];
    }
}
