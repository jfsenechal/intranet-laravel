<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\SmsReminderFactory;
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
    'phone_number',
    'message',
    'reminder_date',
    'other_reminder_date',
    'sent_at',
    'result',
    'updated_by',
])]
#[Table(name: 'sms_reminders')]
#[UseFactory(SmsReminderFactory::class)]
final class SmsReminder extends Model
{
    use HasFactory;

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
