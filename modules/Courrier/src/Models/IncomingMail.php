<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\IncomingMailFactory;
use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Jobs\IndexIncomingMailJob;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[UseFactory(IncomingMailFactory::class)]
#[ScopedBy([DepartmentScope::class])]
#[Connection('maria-courrier')]
#[Fillable([
    'category_id',
    'reference_number',
    'sender',
    'description',
    'mail_date',
    'is_notified',
    'is_registered',
    'has_acknowledgment',
    'user_add',
    'department',
    'follow_up_note',
])]
final class IncomingMail extends Model
{
    use HasFactory;
    use HasUserAdd;
    use SoftDeletes;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'incoming_mail_service')
            ->using(IncomingMailService::class)
            ->withPivot('is_primary');
    }

    public function primaryService(): BelongsToMany
    {
        return $this->services()->wherePivot('is_primary', true);
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'incoming_mail_recipient')
            ->using(IncomingMailRecipient::class)
            ->withPivot('is_primary');
    }

    public function primaryRecipient(): BelongsToMany
    {
        return $this->recipients()->wherePivot('is_primary', true);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();

        self::creating(function (IncomingMail $model): void {
            if (empty($model->department)) {
                $department = DepartmentScope::getCurrentAdminUserDepartment();
                if ($department) {
                    $model->department = $department->value;
                }
            }

            if ($model->department === DepartmentCourrierEnum::CPAS->value) {
                $model->reference_number = (string) self::nextCpasReferenceNumber();
            }
        });

        self::created(function (IncomingMail $model): void {
            IndexIncomingMailJob::dispatch($model->id)->afterCommit();
        });

        self::deleted(function (IncomingMail $model): void {
            IndexIncomingMailJob::dispatch($model->id)->afterCommit();
        });
    }

    protected static function newFactory(): IncomingMailFactory
    {
        return IncomingMailFactory::new();
    }

    protected function casts(): array
    {
        return [
            'mail_date' => 'date',
            'is_notified' => 'boolean',
            'is_registered' => 'boolean',
            'has_acknowledgment' => 'boolean',
        ];
    }

    /**
     * Compute the next sequential reference number for the CPAS department.
     *
     * Numbers are stored as strings but compared numerically so "9" is followed
     * by "10" rather than being ordered lexicographically.
     */
    private static function nextCpasReferenceNumber(): int
    {
        $last = self::withoutGlobalScopes()
            ->where('department', DepartmentCourrierEnum::CPAS->value)
            ->max(DB::raw('CAST(reference_number AS UNSIGNED)'));

        return (int) $last + 1;
    }
}
