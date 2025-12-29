<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Sms extends Model
{
    protected $connection = 'maria-hrm';

    protected $table = 'sms';

    protected $fillable = [
        'employee_id',
        'phone_number',
        'message',
        'reminder_date',
        'other_reminder_date',
        'sent_at',
        'result',
        'updated_by',
    ];

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected function casts(): array
    {
        return [
            'reminder_date' => 'date',
            'other_reminder_date' => 'date',
            'sent_at' => 'date',
        ];
    }
}
